<?php

declare(strict_types=1);

use Dunn\QrCode\Style\EyeStyle\CircleEyeInner;
use Dunn\QrCode\Style\EyeStyle\SquareEyeInner;

it('SquareEyeInner renders a 3x3 square offset by +2 from the 7x7 origin', function (): void {
    // Origin (4, 4) → inner 3x3 at (6, 6).
    expect((new SquareEyeInner())->svgPath(4, 4))->toBe('M6 6h3v3h-3z');
});

it('SquareEyeInner hints crispEdges rendering', function (): void {
    expect((new SquareEyeInner())->shapeRendering())->toBe('crispEdges');
});

it('CircleEyeInner renders a 1.5-radius circle centered in the 7x7', function (): void {
    $path = (new CircleEyeInner())->svgPath(4, 4);
    expect($path)->toContain('a1.5 1.5');
});

it('CircleEyeInner hints geometricPrecision rendering', function (): void {
    expect((new CircleEyeInner())->shapeRendering())->toBe('geometricPrecision');
});
