<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Encoder\BlockInterleaver;
use Dunn\QrCode\Encoder\DataEncoder;
use Dunn\QrCode\ErrorCorrection\GeneratorPolynomial;
use Dunn\QrCode\ErrorCorrection\ReedSolomon;
use Dunn\QrCode\Math\GaloisField256;
use Dunn\QrCode\Matrix\MatrixBuilder;

function buildV1MHelloWorldMatrix(): \Dunn\QrCode\Matrix\Matrix
{
    $encoded = (new DataEncoder())->encode('HELLO WORLD', EccLevel::Medium);
    $gf = new GaloisField256();
    $rs = new ReedSolomon($gf, new GeneratorPolynomial($gf));
    $stream = (new BlockInterleaver($rs))->interleave($encoded->codewords, 1, EccLevel::Medium);

    return (new MatrixBuilder())->build(1, $stream);
}

it('produces a 21×21 V1 matrix for HELLO WORLD at ECC M', function (): void {
    $m = buildV1MHelloWorldMatrix();
    expect($m->size())->toBe(21);
});

it('reserves exactly the expected count of function-pattern cells (V1: 233)', function (): void {
    // V1 totals: 21×21 = 441 modules.
    // Function patterns + format-info reservation = 233 cells.
    // Unreserved (data) cells = 441 - 233 = 208 = 26 codewords × 8 bits, matching V1-M.
    $m = buildV1MHelloWorldMatrix();

    $reserved = 0;
    for ($r = 0; $r < $m->size(); $r++) {
        for ($c = 0; $c < $m->size(); $c++) {
            if ($m->isReserved($r, $c)) {
                $reserved++;
            }
        }
    }

    expect($reserved)->toBe(233);
});

it('preserves the canonical finder + dark module after the full build', function (): void {
    $m = buildV1MHelloWorldMatrix();

    // All three finder corners dark.
    expect($m->get(0, 0))->toBeTrue();
    expect($m->get(0, 20))->toBeTrue();
    expect($m->get(20, 0))->toBeTrue();
    // Dark module at (13, 8) for V1.
    expect($m->get(13, 8))->toBeTrue();
    // Timing pattern: (6, 8) dark, (6, 9) light.
    expect($m->get(6, 8))->toBeTrue();
    expect($m->get(6, 9))->toBeFalse();
});

it('does not write into reserved cells when placing data', function (): void {
    // The data placer must skip reserved cells. We can verify this indirectly by
    // confirming the function patterns we already asserted above are intact.
    $m = buildV1MHelloWorldMatrix();

    // Top-left finder inner 3x3 is dark (would be corrupted if data overwrote it).
    for ($r = 2; $r <= 4; $r++) {
        for ($c = 2; $c <= 4; $c++) {
            expect($m->get($r, $c))->toBeTrue("TL inner ({$r},{$c})");
        }
    }
});

it('builds successfully across a sample of versions and ECC levels', function (): void {
    $gf = new GaloisField256();
    $rs = new ReedSolomon($gf, new GeneratorPolynomial($gf));
    $interleaver = new BlockInterleaver($rs);
    $encoder = new DataEncoder();
    $builder = new MatrixBuilder();

    foreach ([1, 5, 10, 20] as $v) {
        foreach (EccLevel::cases() as $ecc) {
            $encoded = $encoder->encode('TEST', $ecc, forceVersion: $v);
            $stream = $interleaver->interleave($encoded->codewords, $v, $ecc);
            $m = $builder->build($v, $stream);

            expect($m->size())->toBe(4 * $v + 17, "V{$v}-{$ecc->value}");
            // V7+ has version info reserved — easy spot-check for V20.
            if ($v >= 7) {
                expect($m->isReserved(0, $m->size() - 11))->toBeTrue("V{$v}-{$ecc->value} version info");
            }
        }
    }
});
