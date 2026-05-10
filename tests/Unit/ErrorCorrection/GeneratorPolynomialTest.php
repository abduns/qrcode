<?php

declare(strict_types=1);

use Dunn\QrCode\ErrorCorrection\GeneratorPolynomial;
use Dunn\QrCode\Math\GaloisField256;

/**
 * Helper: build expected coefficients from α-powers (highest-degree-first).
 *
 * @param GaloisField256 $gf
 * @param list<int> $powers
 * @return list<int>
 */
function expectedFromPowers(GaloisField256 $gf, array $powers): array
{
    return array_map(fn (int $p): int => $gf->exp($p), $powers);
}

it('builds the degree-7 generator matching ISO 18004 Annex A', function (): void {
    // Annex A α-powers for the degree-7 generator.
    $gf = new GaloisField256();
    $gp = new GeneratorPolynomial($gf);

    $expected = expectedFromPowers($gf, [0, 87, 229, 146, 149, 238, 102, 21]);

    expect($gp->forDegree(7))->toBe($expected);
});

it('builds the degree-10 generator matching the canonical V1-M values', function (): void {
    $gf = new GaloisField256();
    $gp = new GeneratorPolynomial($gf);

    $expected = expectedFromPowers($gf, [0, 251, 67, 46, 61, 118, 70, 64, 94, 32, 45]);

    expect($gp->forDegree(10))->toBe($expected);
});

it('builds the degree-18 generator matching ISO 18004 Annex A', function (): void {
    $gf = new GaloisField256();
    $gp = new GeneratorPolynomial($gf);

    $expected = expectedFromPowers($gf, [
        0, 215, 234, 158, 94, 184, 97, 118, 170, 79, 187, 152, 148, 252, 179, 5, 98, 96, 153,
    ]);

    expect($gp->forDegree(18))->toBe($expected);
});

it('caches polynomials so repeated calls return identical arrays', function (): void {
    $gp = new GeneratorPolynomial(new GaloisField256());

    $first = $gp->forDegree(10);
    $second = $gp->forDegree(10);

    expect($second)->toBe($first);
});

it('always produces a polynomial of length degree + 1 with leading 1', function (): void {
    $gp = new GeneratorPolynomial(new GaloisField256());

    foreach ([7, 10, 13, 15, 16, 17, 18, 20, 22, 24, 26, 28, 30] as $degree) {
        $poly = $gp->forDegree($degree);
        expect($poly)->toHaveCount($degree + 1);
        expect($poly[0])->toBe(1);
    }
});
