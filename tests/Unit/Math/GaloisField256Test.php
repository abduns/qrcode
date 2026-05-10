<?php

declare(strict_types=1);

use Dunn\QrCode\Math\GaloisField256;

it('returns 0 when either operand is 0', function (): void {
    $gf = new GaloisField256();

    expect($gf->multiply(0, 0xCA))->toBe(0);
    expect($gf->multiply(0xCA, 0))->toBe(0);
    expect($gf->multiply(0, 0))->toBe(0);
});

it('treats 1 as the multiplicative identity', function (): void {
    $gf = new GaloisField256();

    for ($a = 1; $a < 256; $a++) {
        expect($gf->multiply(1, $a))->toBe($a);
        expect($gf->multiply($a, 1))->toBe($a);
    }
});

it('multiplies under the QR Code primitive polynomial 0x11D', function (): void {
    $gf = new GaloisField256();

    // 2 * 128 = 256; reduce mod 0x11D → 256 XOR 0x11D = 0x1D = 29.
    // (This identity is QR-specific — under AES's 0x11B it would be different.)
    expect($gf->multiply(2, 128))->toBe(0x1D);

    // α^i and α^(255-i) are multiplicative inverses (cyclic order 255).
    expect($gf->multiply($gf->exp(1), $gf->exp(254)))->toBe(1);
    expect($gf->multiply($gf->exp(100), $gf->exp(155)))->toBe(1);
});

it('reduces alpha^8 to 0x1D under primitive polynomial 0x11D', function (): void {
    $gf = new GaloisField256();

    expect($gf->exp(8))->toBe(0x1D);
});

it('treats alpha as having cyclic order 255', function (): void {
    $gf = new GaloisField256();

    expect($gf->exp(0))->toBe(1);
    expect($gf->exp(255))->toBe(1);
    expect($gf->exp(-1))->toBe($gf->exp(254));
});

it('round-trips log and exp for every non-zero element', function (): void {
    $gf = new GaloisField256();

    for ($a = 1; $a < 256; $a++) {
        expect($gf->exp($gf->log($a)))->toBe($a);
    }
});

it('throws when log(0) is requested', function (): void {
    $gf = new GaloisField256();

    expect(fn () => $gf->log(0))->toThrow(InvalidArgumentException::class);
});

it('throws on division by zero', function (): void {
    $gf = new GaloisField256();

    expect(fn () => $gf->divide(0xCA, 0))->toThrow(InvalidArgumentException::class);
});

it('divides correctly: a / b * b == a', function (): void {
    $gf = new GaloisField256();

    for ($a = 0; $a < 256; $a++) {
        for ($b = 1; $b < 256; $b++) {
            expect($gf->multiply($gf->divide($a, $b), $b))->toBe($a);
        }
    }
});
