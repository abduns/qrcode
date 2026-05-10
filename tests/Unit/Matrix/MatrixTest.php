<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\Matrix;

it('sizes the matrix to 4*version + 17 modules per side', function (): void {
    expect((new Matrix(1))->size())->toBe(21);
    expect((new Matrix(2))->size())->toBe(25);
    expect((new Matrix(7))->size())->toBe(45);
    expect((new Matrix(40))->size())->toBe(177);
});

it('starts every module light and unreserved', function (): void {
    $m = new Matrix(1);
    for ($r = 0; $r < $m->size(); $r++) {
        for ($c = 0; $c < $m->size(); $c++) {
            expect($m->get($r, $c))->toBeFalse();
            expect($m->isReserved($r, $c))->toBeFalse();
        }
    }
});

it('set() updates the module without reserving', function (): void {
    $m = new Matrix(1);
    $m->set(3, 4, true);

    expect($m->get(3, 4))->toBeTrue();
    expect($m->isReserved(3, 4))->toBeFalse();
});

it('reserve() marks reserved without changing module', function (): void {
    $m = new Matrix(1);
    $m->reserve(3, 4);

    expect($m->get(3, 4))->toBeFalse();
    expect($m->isReserved(3, 4))->toBeTrue();
});

it('setFunction() both writes and reserves', function (): void {
    $m = new Matrix(1);
    $m->setFunction(3, 4, true);

    expect($m->get(3, 4))->toBeTrue();
    expect($m->isReserved(3, 4))->toBeTrue();
});

it('rejects out-of-range versions', function (): void {
    expect(fn () => new Matrix(0))->toThrow(InvalidArgumentException::class);
    expect(fn () => new Matrix(41))->toThrow(InvalidArgumentException::class);
});

it('renders a small ASCII representation for debugging', function (): void {
    $m = new Matrix(1);
    $m->set(0, 0, true);
    $ascii = $m->toAscii('X', '.');
    $rows = explode("\n", $ascii);

    expect($rows)->toHaveCount(21);
    expect($rows[0][0])->toBe('X');
    expect($rows[0][1])->toBe('.');
});
