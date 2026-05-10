<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\FinderPattern;
use Dunn\QrCode\Matrix\Matrix;
use Dunn\QrCode\Matrix\Separator;

it('places light separators on the inside edges of all three finders', function (): void {
    $m = new Matrix(1);
    (new FinderPattern())->placeOn($m);
    (new Separator())->placeOn($m);

    $size = $m->size(); // 21

    // Top-left separator: row 7 cols 0..7, col 7 rows 0..6. All light, all reserved.
    for ($c = 0; $c < 8; $c++) {
        expect($m->get(7, $c))->toBeFalse("(7,{$c})");
        expect($m->isReserved(7, $c))->toBeTrue();
    }
    for ($r = 0; $r < 7; $r++) {
        expect($m->get($r, 7))->toBeFalse("({$r},7)");
        expect($m->isReserved($r, 7))->toBeTrue();
    }

    // Top-right separator: row 7 cols size-8..size-1, col size-8 rows 0..6.
    for ($c = $size - 8; $c < $size; $c++) {
        expect($m->get(7, $c))->toBeFalse("(7,{$c})");
    }
    for ($r = 0; $r < 7; $r++) {
        expect($m->get($r, $size - 8))->toBeFalse("({$r},".($size - 8).')');
    }

    // Bottom-left separator: row size-8 cols 0..7, col 7 rows size-7..size-1.
    for ($c = 0; $c < 8; $c++) {
        expect($m->get($size - 8, $c))->toBeFalse();
    }
    for ($r = $size - 7; $r < $size; $r++) {
        expect($m->get($r, 7))->toBeFalse();
    }
});
