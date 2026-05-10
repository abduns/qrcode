<?php

declare(strict_types=1);

use Dunn\QrCode\Style\ModuleShape\ModuleNeighbours;
use Dunn\QrCode\Style\ModuleShape\RoundedModule;

it('renders a full circle for an isolated module (no neighbours)', function (): void {
    $path = (new RoundedModule())->svgPath(5, 7, ModuleNeighbours::isolated());

    // All 4 corners arc-rounded → 4 "a 0.5 0.5 0 0 1" segments, no L commands.
    expect(substr_count($path, 'a0.5 0.5 0 0 1'))->toBe(4);
    expect($path)->not->toContain('L');
});

it('renders a plain square when all four sides have neighbours', function (): void {
    $path = (new RoundedModule())->svgPath(5, 7, new ModuleNeighbours(true, true, true, true));

    // All 4 corners square → 8 L commands (2 per corner), no arc segments.
    expect(substr_count($path, 'L'))->toBe(8);
    expect($path)->not->toContain('a0.5 0.5');
});

it('rounds only the TL corner when the module has neighbours on right and bottom', function (): void {
    // A module that's the top-left of an L: right neighbour, bottom neighbour, no top/left.
    $path = (new RoundedModule())->svgPath(0, 0, new ModuleNeighbours(false, true, true, false));

    // 1 rounded corner (TL), 3 square corners → 6 L commands + 1 arc.
    expect(substr_count($path, 'L'))->toBe(6);
    expect(substr_count($path, 'a0.5 0.5 0 0 1'))->toBe(1);
});

it('produces a pill (stadium) shape for a horizontal 1-row run', function (): void {
    // Middle module of "███": left and right neighbours, no top/bottom.
    $middle = (new RoundedModule())->svgPath(1, 0, new ModuleNeighbours(false, true, false, true));
    // → No corners get rounded (every corner has at least one adjacent neighbour).
    expect($middle)->not->toContain('a0.5 0.5');

    // Left end: no left neighbour, right neighbour present, no top/bottom.
    $left = (new RoundedModule())->svgPath(0, 0, new ModuleNeighbours(false, true, false, false));
    // → TL and BL rounded (those corners have BOTH top/left and bottom/left absent).
    expect(substr_count($left, 'a0.5 0.5 0 0 1'))->toBe(2);
});

it('hints geometricPrecision rendering', function (): void {
    expect((new RoundedModule())->shapeRendering())->toBe('geometricPrecision');
});

it('ModuleNeighbours::isolated() returns all-false', function (): void {
    $n = ModuleNeighbours::isolated();
    expect($n->top)->toBeFalse();
    expect($n->right)->toBeFalse();
    expect($n->bottom)->toBeFalse();
    expect($n->left)->toBeFalse();
});
