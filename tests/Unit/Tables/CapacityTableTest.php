<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Tables\CapacityTable;

it('returns the canonical V1 capacities (Annex I worked examples)', function (): void {
    expect(CapacityTable::dataCodewords(1, EccLevel::Low))->toBe(19);
    expect(CapacityTable::dataCodewords(1, EccLevel::Medium))->toBe(16);
    expect(CapacityTable::dataCodewords(1, EccLevel::Quartile))->toBe(13);
    expect(CapacityTable::dataCodewords(1, EccLevel::High))->toBe(9);
});

it('returns the V40 capacities (largest QR)', function (): void {
    expect(CapacityTable::dataCodewords(40, EccLevel::Low))->toBe(2956);
    expect(CapacityTable::dataCodewords(40, EccLevel::Medium))->toBe(2334);
    expect(CapacityTable::dataCodewords(40, EccLevel::Quartile))->toBe(1666);
    expect(CapacityTable::dataCodewords(40, EccLevel::High))->toBe(1276);
});

it('always returns capacityBits == codewords * 8', function (): void {
    foreach ([1, 5, 10, 20, 40] as $v) {
        foreach (EccLevel::cases() as $ecc) {
            expect(CapacityTable::dataCapacityBits($v, $ecc))
                ->toBe(CapacityTable::dataCodewords($v, $ecc) * 8);
        }
    }
});

it('rejects out-of-range versions', function (): void {
    expect(fn () => CapacityTable::dataCodewords(0, EccLevel::Low))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => CapacityTable::dataCodewords(41, EccLevel::Low))
        ->toThrow(InvalidArgumentException::class);
});

it('strictly decreases capacity as ECC level rises (within each version)', function (): void {
    for ($v = 1; $v <= 40; $v++) {
        $l = CapacityTable::dataCodewords($v, EccLevel::Low);
        $m = CapacityTable::dataCodewords($v, EccLevel::Medium);
        $q = CapacityTable::dataCodewords($v, EccLevel::Quartile);
        $h = CapacityTable::dataCodewords($v, EccLevel::High);

        expect($l > $m && $m > $q && $q > $h)->toBeTrue("version {$v}: {$l} {$m} {$q} {$h}");
    }
});
