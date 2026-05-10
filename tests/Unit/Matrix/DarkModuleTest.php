<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\DarkModule;
use Dunn\QrCode\Matrix\Matrix;

it('places the dark module at (4*version + 9, 8) for V1', function (): void {
    $m = new Matrix(1);
    (new DarkModule())->placeOn($m, 1);

    expect($m->get(13, 8))->toBeTrue();
    expect($m->isReserved(13, 8))->toBeTrue();
});

it('places the dark module at the right row for V7 and V40', function (): void {
    $m7 = new Matrix(7);
    (new DarkModule())->placeOn($m7, 7);
    expect($m7->get(37, 8))->toBeTrue();

    $m40 = new Matrix(40);
    (new DarkModule())->placeOn($m40, 40);
    expect($m40->get(169, 8))->toBeTrue();
});
