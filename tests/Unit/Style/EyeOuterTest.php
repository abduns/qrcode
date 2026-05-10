<?php

declare(strict_types=1);

use Dunn\QrCode\Style\EyeStyle\CircleEyeOuter;
use Dunn\QrCode\Style\EyeStyle\SquareEyeOuter;

it('SquareEyeOuter renders the 7x7 ring as two rects (outer + 5x5 hole)', function (): void {
    expect((new SquareEyeOuter())->svgPath(4, 4))->toBe('M4 4h7v7h-7zM5 5h5v5h-5z');
});

it('SquareEyeOuter does NOT draw the inner 3x3', function (): void {
    $path = (new SquareEyeOuter())->svgPath(4, 4);
    expect($path)->not->toContain('h3v3h-3z');
});

it('SquareEyeOuter hints crispEdges rendering', function (): void {
    expect((new SquareEyeOuter())->shapeRendering())->toBe('crispEdges');
});

it('CircleEyeOuter renders two circles (outer 3.5 + hole 2.5)', function (): void {
    $path = (new CircleEyeOuter())->svgPath(4, 4);
    expect($path)->toContain('a3.5 3.5');
    expect($path)->toContain('a2.5 2.5');
});

it('CircleEyeOuter does NOT draw the inner 1.5 dot', function (): void {
    $path = (new CircleEyeOuter())->svgPath(4, 4);
    expect($path)->not->toContain('a1.5 1.5');
});

it('CircleEyeOuter hints geometricPrecision rendering', function (): void {
    expect((new CircleEyeOuter())->shapeRendering())->toBe('geometricPrecision');
});
