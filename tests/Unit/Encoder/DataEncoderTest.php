<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Encoder\DataEncoder;
use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\Exception\DataTooLongException;

it('encodes V1-M "HELLO WORLD" to the 16 canonical data codewords (Annex I)', function (): void {
    $result = (new DataEncoder())->encode('HELLO WORLD', EccLevel::Medium);

    expect($result->version)->toBe(1);
    expect($result->mode)->toBe(Mode::Alphanumeric);
    expect($result->codewords)->toBe([
        32, 91, 11, 120, 209, 114, 220, 77, 67, 64, 236, 17, 236, 17, 236, 17,
    ]);
});

it('respects forceVersion', function (): void {
    $result = (new DataEncoder())->encode('HELLO WORLD', EccLevel::Medium, forceVersion: 5);
    expect($result->version)->toBe(5);
    // V5-M holds 86 data codewords.
    expect($result->codewords)->toHaveCount(86);
});

it('respects forceMode', function (): void {
    $result = (new DataEncoder())->encode('HI', EccLevel::Low, forceMode: Mode::Byte);
    expect($result->mode)->toBe(Mode::Byte);
});

it('pads numeric "01234567" at V1-H exactly per Thonky', function (): void {
    // Thonky's numeric example: V1-H, "01234567"
    // 01234567 numeric: 012, 345, 67 → 10 + 10 + 7 = 27 bits payload.
    // V1-H = 9 codewords = 72 bits.
    // 4 (mode) + 10 (count for V1 numeric) + 27 = 41 bits used.
    // Plus 4-bit terminator → 45 bits, pad to byte boundary → 48 bits, then 3 pad bytes (EC, 11, EC).
    $result = (new DataEncoder())->encode('01234567', EccLevel::High);

    expect($result->version)->toBe(1);
    expect($result->mode)->toBe(Mode::Numeric);
    expect($result->codewords)->toHaveCount(9);
    // First 6 bytes are deterministic; last 3 are pad bytes.
    expect(array_slice($result->codewords, -3))->toBe([0xEC, 0x11, 0xEC]);
});

it('throws DataTooLongException when forceVersion is too small', function (): void {
    expect(fn () => (new DataEncoder())->encode(str_repeat('a', 100), EccLevel::High, forceVersion: 1))
        ->toThrow(DataTooLongException::class);
});

it('produces a codeword stream of exactly the (version, ECC) capacity', function (): void {
    foreach ([1, 5, 10, 20] as $v) {
        foreach (EccLevel::cases() as $ecc) {
            $result = (new DataEncoder())->encode('A', $ecc, forceVersion: $v);
            $expected = \Dunn\QrCode\Tables\CapacityTable::dataCodewords($v, $ecc);
            expect($result->codewords)->toHaveCount($expected, "V{$v}-{$ecc->value}");
        }
    }
});
