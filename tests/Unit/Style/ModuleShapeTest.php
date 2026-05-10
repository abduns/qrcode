<?php

declare(strict_types=1);

use Dunn\QrCode\Style\ModuleShape\DotModule;
use Dunn\QrCode\Style\ModuleShape\ModuleNeighbours;
use Dunn\QrCode\Style\ModuleShape\SquareModule;

it('SquareModule renders a 1x1 axis-aligned rectangle path', function (): void {
    expect((new SquareModule())->svgPath(5, 7, ModuleNeighbours::isolated()))
        ->toBe('M5 7h1v1h-1z');
});

it('SquareModule ignores neighbour information', function (): void {
    $shape = new SquareModule();
    $isolated = $shape->svgPath(5, 7, ModuleNeighbours::isolated());
    $surrounded = $shape->svgPath(5, 7, new ModuleNeighbours(true, true, true, true));

    expect($isolated)->toBe($surrounded);
});

it('SquareModule hints crispEdges rendering', function (): void {
    expect((new SquareModule())->shapeRendering())->toBe('crispEdges');
});

it('DotModule renders a 0.5-radius circle inscribed in the module', function (): void {
    $path = (new DotModule())->svgPath(5, 7, ModuleNeighbours::isolated());
    expect($path)->toBe('M5.5 7a.5 .5 0 1 1 0 1a.5 .5 0 1 1 0 -1z');
});

it('DotModule ignores neighbour information', function (): void {
    $shape = new DotModule();
    $isolated = $shape->svgPath(5, 7, ModuleNeighbours::isolated());
    $surrounded = $shape->svgPath(5, 7, new ModuleNeighbours(true, true, true, true));

    expect($isolated)->toBe($surrounded);
});

it('DotModule hints geometricPrecision rendering', function (): void {
    expect((new DotModule())->shapeRendering())->toBe('geometricPrecision');
});
