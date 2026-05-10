<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\Matrix;
use Dunn\QrCode\Matrix\TimingPattern;

it('alternates dark/light starting and ending dark in row 6 and column 6 (V1)', function (): void {
    $m = new Matrix(1);
    (new TimingPattern())->placeOn($m);

    $size = $m->size(); // 21

    // Row 6: cols 8..size-9 = 8..12. Even-indexed cols dark, odd light.
    for ($c = 8; $c <= $size - 9; $c++) {
        $expected = $c % 2 === 0;
        expect($m->get(6, $c))->toBe($expected, "row 6 col {$c}");
        expect($m->isReserved(6, $c))->toBeTrue();
    }
    // First and last in the timing run are dark.
    expect($m->get(6, 8))->toBeTrue();
    expect($m->get(6, $size - 9))->toBeTrue();

    // Column 6 mirrors the same pattern.
    for ($r = 8; $r <= $size - 9; $r++) {
        expect($m->get($r, 6))->toBe($r % 2 === 0);
    }
});

it('places a longer timing run for higher versions', function (): void {
    $m = new Matrix(7);
    (new TimingPattern())->placeOn($m);

    $size = $m->size(); // 45

    // Just spot-check that endpoints are dark.
    expect($m->get(6, 8))->toBeTrue();
    expect($m->get(6, $size - 9))->toBeTrue();
    expect($m->get(8, 6))->toBeTrue();
    expect($m->get($size - 9, 6))->toBeTrue();
});
