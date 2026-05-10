<?php

declare(strict_types=1);

use Dunn\QrCode\ErrorCorrection\GeneratorPolynomial;
use Dunn\QrCode\ErrorCorrection\ReedSolomon;
use Dunn\QrCode\Math\GaloisField256;

function makeReedSolomon(): ReedSolomon
{
    $gf = new GaloisField256();

    return new ReedSolomon($gf, new GeneratorPolynomial($gf));
}

it('encodes the canonical V1-M "HELLO WORLD" data block to the expected ECC', function (): void {
    // Source: Thonky QR Code Tutorial / ISO 18004 Annex I worked example.
    // V1-M, alphanumeric mode, 16 data codewords + 10 ECC codewords.
    $data = [32, 91, 11, 120, 209, 114, 220, 77, 67, 64, 236, 17, 236, 17, 236, 17];
    $expected = [196, 35, 39, 119, 235, 215, 231, 226, 93, 23];

    expect(makeReedSolomon()->encode($data, 10))->toBe($expected);
});

it('encodes a 15-codeword V5-Q first block to 18 ECC codewords matching Thonky', function (): void {
    // V5-Q first data block (15 codewords), 18 ECC codewords.
    $data = [67, 85, 70, 134, 87, 38, 85, 194, 119, 50, 6, 18, 6, 103, 38];
    $expected = [
        213, 199, 11, 45, 115, 247, 241, 223, 229,
        248, 154, 117, 154, 111, 86, 161, 111, 39,
    ];

    expect(makeReedSolomon()->encode($data, 18))->toBe($expected);
});

it('returns an empty array when zero ECC codewords are requested', function (): void {
    expect(makeReedSolomon()->encode([1, 2, 3], 0))->toBe([]);
});

it('rejects negative ECC counts', function (): void {
    expect(fn () => makeReedSolomon()->encode([1, 2, 3], -1))
        ->toThrow(InvalidArgumentException::class);
});

it('handles all-zero data correctly (ECC must be all zero)', function (): void {
    $rs = makeReedSolomon();
    $data = array_fill(0, 16, 0);

    expect($rs->encode($data, 10))->toBe(array_fill(0, 10, 0));
});
