<?php

declare(strict_types=1);

use Dunn\QrCode\Style\EyeStyle\CircleEye;
use Dunn\QrCode\Style\EyeStyle\SquareEye;

it('SquareEye renders three nested rectangles using evenodd', function (): void {
    expect((new SquareEye())->svgPath(4, 4))
        ->toBe('M4 4h7v7h-7zM5 5h5v5h-5zM6 6h3v3h-3z');
});

it('SquareEye hints crispEdges rendering', function (): void {
    expect((new SquareEye())->shapeRendering())->toBe('crispEdges');
});

it('CircleEye renders three concentric circles', function (): void {
    $path = (new CircleEye())->svgPath(4, 4);
    // Three subpaths: outer (radius 3.5), hole (radius 2.5), dot (radius 1.5).
    expect($path)->toContain('a3.5 3.5');
    expect($path)->toContain('a2.5 2.5');
    expect($path)->toContain('a1.5 1.5');
});

it('CircleEye hints geometricPrecision rendering', function (): void {
    expect((new CircleEye())->shapeRendering())->toBe('geometricPrecision');
});
