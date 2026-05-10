<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Tables\CapacityTable;
use Dunn\QrCode\Tables\EccTable;

it('returns a populated entry for every (version, ECC) combination', function (): void {
    for ($v = 1; $v <= 40; $v++) {
        foreach (EccLevel::cases() as $ecc) {
            $info = EccTable::lookup($v, $ecc);

            expect($info->totalBlocks() > 0)->toBeTrue("V{$v}-{$ecc->value} has zero blocks");
            expect($info->eccPerBlock > 0)->toBeTrue("V{$v}-{$ecc->value} has eccPerBlock=0");
        }
    }
});

it('agrees with CapacityTable on total data codewords for every (version, ECC)', function (): void {
    for ($v = 1; $v <= 40; $v++) {
        foreach (EccLevel::cases() as $ecc) {
            $info = EccTable::lookup($v, $ecc);
            $expected = CapacityTable::dataCodewords($v, $ecc);

            expect($info->totalDataCodewords())
                ->toBe($expected, "V{$v}-{$ecc->value}");
        }
    }
});

it('returns the canonical V1-M layout: 1 block of 16 data + 10 ECC', function (): void {
    $info = EccTable::lookup(1, EccLevel::Medium);

    expect($info->eccPerBlock)->toBe(10);
    expect($info->group1Blocks)->toBe(1);
    expect($info->group1DataPerBlock)->toBe(16);
    expect($info->group2Blocks)->toBe(0);
    expect($info->totalBlocks())->toBe(1);
    expect($info->totalDataCodewords())->toBe(16);
});

it('returns the canonical V5-Q layout: 2 blocks of 15 + 2 blocks of 16, 18 ECC each', function (): void {
    $info = EccTable::lookup(5, EccLevel::Quartile);

    expect($info->eccPerBlock)->toBe(18);
    expect($info->group1Blocks)->toBe(2);
    expect($info->group1DataPerBlock)->toBe(15);
    expect($info->group2Blocks)->toBe(2);
    expect($info->group2DataPerBlock)->toBe(16);
    expect($info->totalBlocks())->toBe(4);
    expect($info->totalDataCodewords())->toBe(62);
});

it('rejects out-of-range versions', function (): void {
    expect(fn () => EccTable::lookup(0, EccLevel::Low))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => EccTable::lookup(41, EccLevel::High))
        ->toThrow(InvalidArgumentException::class);
});
