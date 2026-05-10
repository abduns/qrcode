<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Exception\InvalidConfigurationException;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Png\GdPngRenderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\EyeStyle\CircleEyeInner;
use Dunn\QrCode\Style\EyeStyle\CircleEyeOuter;
use Dunn\QrCode\Style\Logo;
use Dunn\QrCode\Style\ModuleShape\DotModule;

beforeEach(function (): void {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('ext-gd is not loaded');
    }
});

function pngHelloWorld(): QrCode
{
    return QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
}

function tinyPngBytesGd(): string
{
    // 1×1 solid red PNG, produced via GD so it's guaranteed-decodable.
    $img = imagecreatetruecolor(1, 1);
    if ($img === false) {
        throw new RuntimeException('imagecreatetruecolor failed in test fixture.');
    }
    $red = imagecolorallocate($img, 255, 0, 0);
    if ($red === false) {
        imagedestroy($img);
        throw new RuntimeException('imagecolorallocate failed in test fixture.');
    }
    imagefilledrectangle($img, 0, 0, 0, 0, $red);
    ob_start();
    imagepng($img);
    $bytes = ob_get_clean();
    imagedestroy($img);

    return $bytes !== false ? $bytes : '';
}

it('produces PNG bytes (starting with the PNG magic header)', function (): void {
    $png = (new GdPngRenderer())->render(pngHelloWorld());
    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});

it('produces an image of the requested approximate size', function (): void {
    $png = (new GdPngRenderer(size: 290, margin: 4))->render(pngHelloWorld());

    $img = imagecreatefromstring($png);
    expect($img)->not->toBeFalse();
    if ($img === false) {
        return;
    }
    // V1 (21 modules) + 8 margin = 29 cells. floor(290/29) = 10 → 290 px.
    expect(imagesx($img))->toBe(290);
    expect(imagesy($img))->toBe(290);
    imagedestroy($img);
});

it('reports MIME type image/png', function (): void {
    expect((new GdPngRenderer())->mimeType())->toBe('image/png');
});

it('rejects nonsensical sizes/margins', function (): void {
    expect(fn () => new GdPngRenderer(size: 0))->toThrow(InvalidArgumentException::class);
    expect(fn () => new GdPngRenderer(margin: -1))->toThrow(InvalidArgumentException::class);
});

it('rejects malformed hex colors at construction time', function (): void {
    expect(fn () => new GdPngRenderer(foreground: 'not-a-color'))
        ->toThrow(InvalidArgumentException::class);
});

it('accepts 3-digit shorthand hex', function (): void {
    $png = (new GdPngRenderer(foreground: '#abc'))->render(pngHelloWorld());
    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});

it('accepts Color instances for every colour parameter', function (): void {
    $png = (new GdPngRenderer(
        foreground: Color::hex('#264653'),
        background: Color::white(),
        dotColor: Color::hex('#2a9d8f'),
        markerOuterColor: Color::hex('#e76f51'),
        markerInnerColor: Color::hex('#f4a261'),
    ))->render(pngHelloWorld());

    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});

it('renders DotModule + CircleEyeOuter + CircleEyeInner without erroring', function (): void {
    $png = (new GdPngRenderer(
        moduleShape: new DotModule(),
        eyeOuter: new CircleEyeOuter(),
        eyeInner: new CircleEyeInner(),
    ))->render(pngHelloWorld());

    $img = imagecreatefromstring($png);
    expect($img)->not->toBeFalse();
    if ($img !== false) {
        imagedestroy($img);
    }
});

it('paints the three regions with distinct colours when configured', function (): void {
    $png = (new GdPngRenderer(
        size: 290,
        margin: 4,
        background: Color::white(),
        dotColor: Color::rgb(0x26, 0x46, 0x53),
        markerOuterColor: Color::rgb(0x2a, 0x9d, 0x8f),
        markerInnerColor: Color::rgb(0xe7, 0x6f, 0x51),
    ))->render(pngHelloWorld());

    $img = imagecreatefromstring($png);
    expect($img)->not->toBeFalse();
    if ($img === false) {
        return;
    }

    $sample = function (GdImage $img, int $x, int $y): array {
        $idx = imagecolorat($img, $x, $y);
        if ($idx === false) {
            throw new RuntimeException("imagecolorat({$x},{$y}) failed");
        }
        return imagecolorsforindex($img, $idx);
    };

    // V1 → 21 modules, margin 4, scale 10 px. Module (10, 10) maps to
    // pixel ((10+4)*10 + 5, (10+4)*10 + 5) = (145, 145).
    $dotRgb = $sample($img, 145, 145);
    // Sample the inner finder pixel (center of top-left finder ~3, 3).
    $innerRgb = $sample($img, 75, 75);
    // Sample an outer finder pixel (~0, 3 of top-left finder).
    $outerRgb = $sample($img, 45, 75);

    expect($innerRgb['red'])->toBe(0xe7);
    expect($outerRgb['red'])->toBe(0x2a);
    // Data dot may be background OR dotColor depending on whether (10,10) is
    // dark; if dark it will match the dot colour.
    expect(in_array($dotRgb['red'], [0x26, 0xff], true))->toBeTrue();

    imagedestroy($img);
});

it('embeds a raster logo at the center', function (): void {
    $logo = new Logo(tinyPngBytesGd(), 'image/png', sizeRatio: 0.2);

    $png = (new GdPngRenderer(logo: $logo))->render(
        QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Quartile)->build()
    );

    $img = imagecreatefromstring($png);
    expect($img)->not->toBeFalse();
    if ($img === false) {
        return;
    }

    // V1-Q is 21 modules; scale at default size 300 / 29 = 10 px → 290 px wide.
    // The logo (sizeRatio 0.2) is 0.2*210 = 42 px centered at (145, 145).
    // Our tiny logo is a red 1×1 PNG scaled up, so the center pixel should be red.
    $idx = imagecolorat($img, 145, 145);
    if ($idx === false) {
        throw new RuntimeException('imagecolorat failed');
    }
    $center = imagecolorsforindex($img, $idx);
    expect($center['red'])->toBeGreaterThan(200);
    expect($center['green'])->toBeLessThan(50);
    expect($center['blue'])->toBeLessThan(50);

    imagedestroy($img);
});

it('rejects a PNG logo that exceeds the ECC tolerance', function (): void {
    $logo = new Logo(tinyPngBytesGd(), 'image/png', sizeRatio: 0.5);
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Low)->build();

    $renderer = new GdPngRenderer(logo: $logo);

    expect(fn () => $renderer->render($qr))->toThrow(InvalidConfigurationException::class);
});

it('refuses SVG logos (ext-gd cannot decode them)', function (): void {
    $svgLogo = new Logo('<svg xmlns="http://www.w3.org/2000/svg"/>', 'image/svg+xml');

    $renderer = new GdPngRenderer(logo: $svgLogo);

    expect(fn () => $renderer->render(pngHelloWorld()))->toThrow(InvalidConfigurationException::class);
});
