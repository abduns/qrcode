<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Png\GdPngRenderer;

beforeEach(function (): void {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('ext-gd is not loaded');
    }
});

it('produces PNG bytes (starting with the PNG magic header)', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
    $png = (new GdPngRenderer())->render($qr);

    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});

it('produces an image of the requested approximate size', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
    $png = (new GdPngRenderer(size: 290, margin: 4))->render($qr);

    $img = imagecreatefromstring($png);
    expect($img)->not->toBeFalse();
    if ($img === false) {
        return;
    }

    // V1 (21 modules) + 8 margin = 29 cells. floor(290/29) = 10. → 290px.
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

it('rejects malformed hex colors at render time', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
    $renderer = new GdPngRenderer(foreground: 'not-a-color');

    expect(fn () => $renderer->render($qr))->toThrow(InvalidArgumentException::class);
});

it('accepts 3-digit shorthand hex', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
    $png = (new GdPngRenderer(foreground: '#abc'))->render($qr);

    expect(substr($png, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});
