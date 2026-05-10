<?php

declare(strict_types=1);

use Dunn\QrCode\Style\EyeStyle\RoundedEyeInner;
use Dunn\QrCode\Style\EyeStyle\RoundedEyeOuter;

it('RoundedEyeOuter renders a 7x7 rounded ring with 8 corner arcs', function (): void {
    $path = (new RoundedEyeOuter())->svgPath(4, 4);

    // 4 outer corners + 4 inner-hole corners = 8 arc commands.
    expect(substr_count($path, 'a1 1 0 0 1'))->toBe(8);
    // Outer rect starts at (x+1, y) = (5, 4).
    expect($path)->toContain('M5 4');
});

it('RoundedEyeOuter hints geometricPrecision rendering', function (): void {
    expect((new RoundedEyeOuter())->shapeRendering())->toBe('geometricPrecision');
});

it('RoundedEyeInner renders a 3x3 rounded square at the 7x7 centre', function (): void {
    $path = (new RoundedEyeInner())->svgPath(4, 4);

    // 4 corner arcs at radius 0.5.
    expect(substr_count($path, 'a.5 .5 0 0 1'))->toBe(4);
    // Top-mid of inner square sits at (x+2.5, y+2) = (6.5, 6).
    expect($path)->toStartWith('M6.5 6');
});

it('RoundedEyeInner hints geometricPrecision rendering', function (): void {
    expect((new RoundedEyeInner())->shapeRendering())->toBe('geometricPrecision');
});
