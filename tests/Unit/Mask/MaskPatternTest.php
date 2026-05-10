<?php

declare(strict_types=1);

use Dunn\QrCode\Mask\MaskPattern;
use Dunn\QrCode\Matrix\Matrix;

it('Pattern0 inverts cells where (row + col) is even', function (): void {
    expect(MaskPattern::Pattern0->predicate(0, 0))->toBeTrue();
    expect(MaskPattern::Pattern0->predicate(0, 1))->toBeFalse();
    expect(MaskPattern::Pattern0->predicate(1, 1))->toBeTrue();
});

it('Pattern1 inverts even rows', function (): void {
    expect(MaskPattern::Pattern1->predicate(0, 0))->toBeTrue();
    expect(MaskPattern::Pattern1->predicate(0, 5))->toBeTrue();
    expect(MaskPattern::Pattern1->predicate(1, 0))->toBeFalse();
});

it('Pattern2 inverts every 3rd column', function (): void {
    expect(MaskPattern::Pattern2->predicate(0, 0))->toBeTrue();
    expect(MaskPattern::Pattern2->predicate(0, 3))->toBeTrue();
    expect(MaskPattern::Pattern2->predicate(0, 1))->toBeFalse();
});

it('Pattern3 inverts cells where (row + col) is divisible by 3', function (): void {
    expect(MaskPattern::Pattern3->predicate(0, 0))->toBeTrue();
    expect(MaskPattern::Pattern3->predicate(1, 2))->toBeTrue();
    expect(MaskPattern::Pattern3->predicate(0, 1))->toBeFalse();
});

it('Pattern4 follows the spec floor-floor formula', function (): void {
    // (floor(row/2) + floor(col/3)) % 2 == 0
    expect(MaskPattern::Pattern4->predicate(0, 0))->toBeTrue();   // (0 + 0) % 2 = 0
    expect(MaskPattern::Pattern4->predicate(0, 3))->toBeFalse();  // (0 + 1) % 2 = 1
    expect(MaskPattern::Pattern4->predicate(2, 0))->toBeFalse();  // (1 + 0) % 2 = 1
    expect(MaskPattern::Pattern4->predicate(2, 3))->toBeTrue();   // (1 + 1) % 2 = 0
});

it('only inverts non-reserved cells', function (): void {
    $m = new Matrix(1);
    // Mark (0, 0) as reserved.
    $m->setFunction(0, 0, false);

    MaskPattern::Pattern0->applyTo($m);

    // (0, 0) is reserved so untouched.
    expect($m->get(0, 0))->toBeFalse();
    // (1, 1) is unreserved and pattern0 says invert (1+1=2, even), so light → dark.
    expect($m->get(1, 1))->toBeTrue();
});

it('applying the same mask twice returns the matrix to its original state', function (): void {
    $m = new Matrix(1);
    // Pre-set a few unreserved cells to dark.
    $m->set(2, 3, true);
    $m->set(5, 5, true);

    MaskPattern::Pattern3->applyTo($m);
    MaskPattern::Pattern3->applyTo($m);

    expect($m->get(2, 3))->toBeTrue();
    expect($m->get(5, 5))->toBeTrue();
    expect($m->get(0, 0))->toBeFalse();
});
