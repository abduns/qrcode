<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Exception\InvalidConfigurationException;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Svg\SvgRenderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\EyeStyle\CircleEyeInner;
use Dunn\QrCode\Style\EyeStyle\CircleEyeOuter;
use Dunn\QrCode\Style\Logo;
use Dunn\QrCode\Style\ModuleShape\DotModule;

function styledQr(): QrCode
{
    return QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
}

it('emits three independent <path> elements (dots, marker outer, marker inner)', function (): void {
    $svg = (new SvgRenderer())->render(styledQr());

    // Count occurrences of <path
    expect(substr_count($svg, '<path '))->toBe(3);
});

it('falls back to SquareEyeOuter + SquareEyeInner when no eye styles passed', function (): void {
    $svg = (new SvgRenderer())->render(styledQr());

    // Square outer ring (7x7 + 5x5 hole, no inner square in this path).
    expect($svg)->toContain('h7v7h-7z');
    // Square inner (3x3) in the inner-path element.
    expect($svg)->toContain('h3v3h-3z');
});

it('uses circle eyes when CircleEyeOuter + CircleEyeInner are passed', function (): void {
    $svg = (new SvgRenderer(
        eyeOuter: new CircleEyeOuter(),
        eyeInner: new CircleEyeInner(),
    ))->render(styledQr());

    expect($svg)->toContain('a3.5 3.5');
    expect($svg)->toContain('a2.5 2.5');
    expect($svg)->toContain('a1.5 1.5');
});

it('lets the inner and outer marker be styled independently', function (): void {
    $svg = (new SvgRenderer(eyeInner: new CircleEyeInner()))->render(styledQr());

    expect($svg)->toContain('h7v7h-7z');   // square outer
    expect($svg)->toContain('a1.5 1.5');   // circle inner
});

it('applies per-region dotColor / markerOuterColor / markerInnerColor', function (): void {
    $svg = (new SvgRenderer(
        foreground: '#000000',
        dotColor: Color::hex('#3a9'),
        markerOuterColor: '#a33',
        markerInnerColor: Color::hex('#33a'),
    ))->render(styledQr());

    expect($svg)->toContain('fill="#33aa99"');   // dot
    expect($svg)->toContain('fill="#aa3333"');   // marker outer
    expect($svg)->toContain('fill="#3333aa"');   // marker inner
});

it('falls each region back to foreground when its colour is null', function (): void {
    $svg = (new SvgRenderer(
        foreground: Color::hex('#112233'),
        dotColor: '#aabbcc',
        // markerOuterColor + markerInnerColor unset → fall back to foreground
    ))->render(styledQr());

    expect($svg)->toContain('fill="#aabbcc"');           // dot has explicit colour
    expect(substr_count($svg, 'fill="#112233"'))->toBe(2); // both marker regions
});

it('uses fill-rule="evenodd" on every region path', function (): void {
    $svg = (new SvgRenderer())->render(styledQr());

    expect(substr_count($svg, 'fill-rule="evenodd"'))->toBe(3);
});

it('upgrades shape-rendering to geometricPrecision when any region uses curves', function (): void {
    $svg = (new SvgRenderer(eyeOuter: new CircleEyeOuter()))->render(styledQr());
    expect($svg)->toContain('shape-rendering="geometricPrecision"');

    $svg2 = (new SvgRenderer(moduleShape: new DotModule()))->render(styledQr());
    expect($svg2)->toContain('shape-rendering="geometricPrecision"');
});

it('embeds a logo as a data-URI <image> element', function (): void {
    $tinyPng = base64_decode( // 1x1 transparent PNG
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
    );
    $logo = new Logo($tinyPng, 'image/png', sizeRatio: 0.2);

    $svg = (new SvgRenderer(logo: $logo))->render(styledQr());

    expect($svg)->toContain('<image ');
    expect($svg)->toContain('href="data:image/png;base64,');
    expect($svg)->toContain('preserveAspectRatio="xMidYMid meet"');
});

it('rejects a logo that exceeds the ECC tolerance at render time', function (): void {
    $tinyPng = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
    );
    // sizeRatio 0.5 is too big for ECC Low (max 0.26).
    $logo = new Logo($tinyPng, 'image/png', sizeRatio: 0.5);
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Low)->build();

    $renderer = new SvgRenderer(logo: $logo);

    expect(fn () => $renderer->render($qr))->toThrow(InvalidConfigurationException::class);
});

it('keeps default Square+Square output reasonably small (<5KB) for V1-M', function (): void {
    $svg = (new SvgRenderer())->render(styledQr());
    expect(strlen($svg))->toBeLessThan(5000);
});
