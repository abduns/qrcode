<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\FinderPattern;
use Dunn\QrCode\Matrix\Matrix;

it('places three 7×7 finder patterns at the canonical corners', function (): void {
    $m = new Matrix(1);
    (new FinderPattern())->placeOn($m);

    // Top-left finder origin (0,0): outer ring dark, inner 5×5 light, center 3×3 dark.
    foreach ([[0, 0], [0, 6], [6, 0], [6, 6], [3, 3], [3, 4], [4, 3]] as [$r, $c]) {
        expect($m->get($r, $c))->toBeTrue("TL ({$r},{$c}) should be dark");
    }
    foreach ([[1, 1], [1, 5], [5, 1], [5, 5]] as [$r, $c]) {
        expect($m->get($r, $c))->toBeFalse("TL ({$r},{$c}) should be light");
    }

    // Top-right finder origin (0, size-7) for V1 = (0, 14)
    expect($m->get(0, 14))->toBeTrue();
    expect($m->get(0, 20))->toBeTrue();
    expect($m->get(3, 17))->toBeTrue();
    expect($m->get(1, 15))->toBeFalse();

    // Bottom-left finder origin (size-7, 0) for V1 = (14, 0)
    expect($m->get(14, 0))->toBeTrue();
    expect($m->get(20, 0))->toBeTrue();
    expect($m->get(17, 3))->toBeTrue();
    expect($m->get(15, 1))->toBeFalse();
});

it('reserves every cell it places', function (): void {
    $m = new Matrix(1);
    (new FinderPattern())->placeOn($m);

    foreach ([[0, 0], [3, 3], [6, 6]] as [$r, $c]) {
        expect($m->isReserved($r, $c))->toBeTrue("({$r},{$c})");
    }
    expect($m->isReserved(0, 14))->toBeTrue();
    expect($m->isReserved(14, 0))->toBeTrue();
});
