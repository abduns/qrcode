<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Svg\SvgRenderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\Gradient\GradientStop;
use Dunn\QrCode\Style\Gradient\LinearGradient;
use Dunn\QrCode\Style\Gradient\RadialGradient;

function gradientQr(): QrCode
{
    return QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
}

it('renders a linear-gradient fill for dotColor', function (): void {
    $grad = new LinearGradient([
        new GradientStop(0.0, Color::hex('#264653')),
        new GradientStop(1.0, Color::hex('#2a9d8f')),
    ]);

    $svg = (new SvgRenderer(dotColor: $grad))->render(gradientQr());

    // The dot path is the FIRST <path> in the body.
    expect($svg)->toContain('<defs>');
    expect($svg)->toContain('<linearGradient ');
    expect($svg)->toContain('fill="url(#');
});

it('renders a radial-gradient fill for the foreground (applies to all unset regions)', function (): void {
    $grad = new RadialGradient([
        new GradientStop(0.0, Color::hex('#e76f51')),
        new GradientStop(1.0, Color::hex('#264653')),
    ]);

    $svg = (new SvgRenderer(foreground: $grad))->render(gradientQr());

    expect($svg)->toContain('<radialGradient ');
    // The 3 paths (dot/outer/inner) all reference the same url(#…) since
    // none has a per-region override.
    expect(substr_count($svg, 'fill="url(#'))->toBeGreaterThanOrEqual(3);
});

it('uses unique gradient ids per render() call', function (): void {
    $grad = new LinearGradient([
        new GradientStop(0.0, Color::black()),
        new GradientStop(1.0, Color::white()),
    ]);
    $renderer = new SvgRenderer(dotColor: $grad);

    $svg1 = $renderer->render(gradientQr());
    $svg2 = $renderer->render(gradientQr());

    expect(preg_match('/id="(qr-[0-9a-f]+-dot)"/', $svg1, $m1))->toBe(1);
    expect(preg_match('/id="(qr-[0-9a-f]+-dot)"/', $svg2, $m2))->toBe(1);
    expect($m1[1] ?? null)->not->toBe($m2[1] ?? null);
});

it('still emits a flat hex fill when no gradient is supplied', function (): void {
    $svg = (new SvgRenderer(foreground: Color::hex('#112233')))->render(gradientQr());

    expect($svg)->not->toContain('<defs>');
    expect($svg)->not->toContain('<linearGradient');
    expect($svg)->toContain('fill="#112233"');
});

it('combines flat colours and gradients across regions', function (): void {
    $svg = (new SvgRenderer(
        dotColor: new LinearGradient([
            new GradientStop(0.0, Color::hex('#264653')),
            new GradientStop(1.0, Color::hex('#2a9d8f')),
        ]),
        markerOuterColor: Color::hex('#e76f51'),
        markerInnerColor: new RadialGradient([
            new GradientStop(0.0, Color::hex('#f4a261')),
            new GradientStop(1.0, Color::hex('#264653')),
        ]),
    ))->render(gradientQr());

    expect($svg)->toContain('<linearGradient ');
    expect($svg)->toContain('<radialGradient ');
    expect($svg)->toContain('fill="#e76f51"');
    expect(substr_count($svg, 'fill="url(#'))->toBeGreaterThanOrEqual(2);
});
