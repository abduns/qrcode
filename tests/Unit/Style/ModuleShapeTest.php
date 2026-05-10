<?php

declare(strict_types=1);

use Dunn\QrCode\Style\ModuleShape\DotModule;
use Dunn\QrCode\Style\ModuleShape\SquareModule;

it('SquareModule renders a 1x1 axis-aligned rectangle path', function (): void {
    expect((new SquareModule())->svgPath(5, 7))->toBe('M5 7h1v1h-1z');
});

it('SquareModule hints crispEdges rendering', function (): void {
    expect((new SquareModule())->shapeRendering())->toBe('crispEdges');
});

it('DotModule renders a 0.5-radius circle inscribed in the module', function (): void {
    $path = (new DotModule())->svgPath(5, 7);
    expect($path)->toBe('M5.5 7a.5 .5 0 1 1 0 1a.5 .5 0 1 1 0 -1z');
});

it('DotModule hints geometricPrecision rendering', function (): void {
    expect((new DotModule())->shapeRendering())->toBe('geometricPrecision');
});
