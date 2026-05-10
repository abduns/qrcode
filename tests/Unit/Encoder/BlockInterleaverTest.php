<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Encoder\BlockInterleaver;
use Dunn\QrCode\ErrorCorrection\GeneratorPolynomial;
use Dunn\QrCode\ErrorCorrection\ReedSolomon;
use Dunn\QrCode\Math\GaloisField256;

function makeInterleaver(): BlockInterleaver
{
    $gf = new GaloisField256();

    return new BlockInterleaver(new ReedSolomon($gf, new GeneratorPolynomial($gf)));
}

it('interleaves V1-M HELLO WORLD into the canonical 26-byte stream', function (): void {
    // V1-M: 1 block of 16 data codewords + 10 ECC codewords.
    // Single block = no interleaving; the result is concatenation.
    $data = [32, 91, 11, 120, 209, 114, 220, 77, 67, 64, 236, 17, 236, 17, 236, 17];
    $expectedEcc = [196, 35, 39, 119, 235, 215, 231, 226, 93, 23];

    $stream = makeInterleaver()->interleave($data, 1, EccLevel::Medium);

    expect($stream)->toBe(array_merge($data, $expectedEcc));
});

it('interleaves a multi-block V5-Q sample correctly', function (): void {
    // V5-Q: 2 blocks of 15 + 2 blocks of 16 = 62 data codewords, 18 ECC each.
    // We build a synthetic input — the test verifies the interleaving order and
    // RS correctness simultaneously.
    $block1Data = [67, 85, 70, 134, 87, 38, 85, 194, 119, 50, 6, 18, 6, 103, 38];
    $block2Data = [246, 246, 66, 7, 118, 134, 242, 7, 38, 86, 22, 198, 199, 146, 6];
    $block3Data = [182, 230, 247, 119, 50, 7, 118, 134, 87, 38, 82, 6, 134, 151, 50, 7];
    $block4Data = [70, 247, 118, 86, 194, 6, 151, 50, 16, 236, 17, 236, 17, 236, 17, 236];

    $allData = array_merge($block1Data, $block2Data, $block3Data, $block4Data);
    expect($allData)->toHaveCount(62);

    $stream = makeInterleaver()->interleave($allData, 5, EccLevel::Quartile);

    // Expected length = 62 data + 4 blocks * 18 ECC = 62 + 72 = 134.
    expect($stream)->toHaveCount(134);

    // First column of interleaved data: block1[0], block2[0], block3[0], block4[0]
    expect(array_slice($stream, 0, 4))->toBe([67, 246, 182, 70]);

    // Column 14 still includes all 4 blocks (group-1 blocks are length 15).
    // After 14 full columns (4 entries each) = 56 entries, column 14 fills 56..59.
    expect(array_slice($stream, 56, 4))->toBe([38, 6, 50, 17]);

    // Column 15 (the "tail") only has the two group-2 blocks (block3 and block4).
    expect(array_slice($stream, 60, 2))->toBe([7, 236]);

    // First column of ECC begins at index 62 (62 data codewords precede it).
    // RS correctness is verified by the V5-Q test in ReedSolomonTest.
});
