<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\Matrix;

it('clone produces an independent copy (PHP array copy-on-write)', function (): void {
    $original = new Matrix(1);
    $original->set(5, 5, true);
    $original->reserve(3, 3);

    $copy = clone $original;

    // Modify the copy.
    $copy->set(10, 10, true);
    $copy->reserve(7, 7);

    // Original is unaffected.
    expect($original->get(10, 10))->toBeFalse();
    expect($original->isReserved(7, 7))->toBeFalse();

    // Copy retains the original's pre-clone state.
    expect($copy->get(5, 5))->toBeTrue();
    expect($copy->isReserved(3, 3))->toBeTrue();
});
