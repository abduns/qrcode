<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Svg\SvgRenderer;

function buildHelloWorldQr(): QrCode
{
    return QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
}

it('produces a valid <svg> element for V1-M HELLO WORLD', function (): void {
    $svg = (new SvgRenderer())->render(buildHelloWorldQr());

    expect($svg)->toStartWith('<svg ');
    expect($svg)->toEndWith('</svg>');
    expect($svg)->toContain('xmlns="http://www.w3.org/2000/svg"');
});

it('sizes the viewBox to (modules + 2*margin) for V1', function (): void {
    $svg = (new SvgRenderer(margin: 4))->render(buildHelloWorldQr());

    // V1 is 21 modules; with 4-module margin: 21 + 8 = 29.
    expect($svg)->toContain('viewBox="0 0 29 29"');
});

it('applies width/height from the size constructor argument', function (): void {
    $svg = (new SvgRenderer(size: 500))->render(buildHelloWorldQr());

    expect($svg)->toContain('width="500"');
    expect($svg)->toContain('height="500"');
});

it('embeds the foreground/background colors as attribute values', function (): void {
    $svg = (new SvgRenderer(foreground: '#1a1a2e', background: '#fafafa'))
        ->render(buildHelloWorldQr());

    expect($svg)->toContain('fill="#fafafa"');
    expect($svg)->toContain('fill="#1a1a2e"');
});

it('emits a <path> element with run-consolidated d attribute', function (): void {
    $svg = (new SvgRenderer())->render(buildHelloWorldQr());

    // The <path> always contains at least one move ("M") and one horizontal-line ("h") command.
    expect($svg)->toContain('<path d="M');
    expect($svg)->toContain('h');
});

it('reports MIME type image/svg+xml', function (): void {
    expect((new SvgRenderer())->mimeType())->toBe('image/svg+xml');
});

it('keeps SVG output reasonably small for V1-M (<5KB)', function (): void {
    $svg = (new SvgRenderer())->render(buildHelloWorldQr());
    expect(strlen($svg))->toBeLessThan(5000);
});

it('rejects nonsensical sizes/margins', function (): void {
    expect(fn () => new SvgRenderer(size: 0))->toThrow(InvalidArgumentException::class);
    expect(fn () => new SvgRenderer(margin: -1))->toThrow(InvalidArgumentException::class);
});
