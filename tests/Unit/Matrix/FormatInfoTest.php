<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Matrix\FormatInfo;
use Dunn\QrCode\Matrix\Matrix;

it('computes ECC L mask 0 → canonical 0b111011111000100 (Annex C)', function (): void {
    expect(FormatInfo::computeFormatBits(EccLevel::Low->formatBits(), 0))
        ->toBe(0b111011111000100);
});

it('computes ECC M mask 5 → canonical 0b100000011001110', function (): void {
    // Per common references: M (00) mask 5 = 100000011001110 = 0x4067
    expect(FormatInfo::computeFormatBits(EccLevel::Medium->formatBits(), 5))
        ->toBe(0b100000011001110);
});

it('computes ECC H mask 7 → canonical 0b000100000111011', function (): void {
    // H (10) mask 7 = 000100000111011 = 2107 (Thonky format-info table)
    expect(FormatInfo::computeFormatBits(EccLevel::High->formatBits(), 7))
        ->toBe(0b000100000111011);
});

it('reserves 31 cells around the three finders (V1)', function (): void {
    // For V1 (size 21):
    // Top-left format info: row 8 cols 0..8 (skip col 6) = 8 cells; col 8 rows 0..8 (skip row 6) = 8 cells; (8,8) double-counted → 15 cells.
    // Top-right format info: row 8, cols size-8..size-1 = 8 cells.
    // Bottom-left format info: col 8, rows size-7..size-1 = 7 cells.
    // Subtract overlap of top-left's (8, size-8)=(8, 13) ... but size-8 = 13 isn't in 0..8 range, so no overlap.
    // Total: 15 + 8 + 7 = 30 cells.
    $m = new Matrix(1);
    (new FormatInfo())->reserve($m);

    $count = 0;
    for ($r = 0; $r < $m->size(); $r++) {
        for ($c = 0; $c < $m->size(); $c++) {
            if ($m->isReserved($r, $c)) {
                $count++;
            }
        }
    }
    expect($count)->toBe(30);
});

it('rejects invalid mask patterns', function (): void {
    $m = new Matrix(1);
    expect(fn () => (new FormatInfo())->place($m, EccLevel::Low, -1))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => (new FormatInfo())->place($m, EccLevel::Low, 8))
        ->toThrow(InvalidArgumentException::class);
});
