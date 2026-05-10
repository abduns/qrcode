<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\AlignmentPattern;
use Dunn\QrCode\Matrix\Matrix;

it('places no alignment patterns for V1', function (): void {
    $m = new Matrix(1);
    (new AlignmentPattern())->placeOn($m, 1);

    // Whole matrix is still light (no patterns placed).
    for ($r = 0; $r < 21; $r++) {
        for ($c = 0; $c < 21; $c++) {
            expect($m->isReserved($r, $c))->toBeFalse("({$r},{$c})");
        }
    }
});

it('places exactly one alignment pattern at (18, 18) for V2', function (): void {
    $m = new Matrix(2);
    (new AlignmentPattern())->placeOn($m, 2);

    // Center of the alignment pattern at (18, 18) is dark.
    expect($m->get(18, 18))->toBeTrue();
    // Inner ring at distance 1 is light.
    expect($m->get(17, 18))->toBeFalse();
    expect($m->get(18, 17))->toBeFalse();
    // Outer ring at distance 2 is dark.
    expect($m->get(16, 16))->toBeTrue();
    expect($m->get(20, 20))->toBeTrue();
    expect($m->get(16, 20))->toBeTrue();
    expect($m->get(20, 16))->toBeTrue();

    // No other alignment patterns: e.g., (6, 6), (6, 18), (18, 6) overlap finders → skipped.
    // Spot-check that (5, 5) is unreserved (would have been part of (6,6) pattern if placed).
    expect($m->isReserved(5, 5))->toBeFalse();
});

it('places six alignment patterns for V7 (3×3 grid minus 3 finder corners)', function (): void {
    $m = new Matrix(7);
    (new AlignmentPattern())->placeOn($m, 7);

    // V7 positions: [6, 22, 38]; expected centers (excluding (6,6),(6,38),(38,6)):
    // (6,22), (22,6), (22,22), (22,38), (38,22), (38,38).
    foreach ([[6, 22], [22, 6], [22, 22], [22, 38], [38, 22], [38, 38]] as [$r, $c]) {
        expect($m->get($r, $c))->toBeTrue("center ({$r},{$c})");
        expect($m->get($r - 1, $c))->toBeFalse('inner ring');
        expect($m->get($r - 2, $c))->toBeTrue('outer ring');
    }

    // The skipped corners — patterns NOT placed at (6,6), (6,38), (38,6).
    expect($m->isReserved(8, 8))->toBeFalse();   // would be outer ring of (6,6)
    expect($m->isReserved(8, 36))->toBeFalse();  // would be outer ring of (6,38)
    expect($m->isReserved(36, 8))->toBeFalse();  // would be outer ring of (38,6)
});
