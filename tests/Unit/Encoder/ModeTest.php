<?php

declare(strict_types=1);

use Dunn\QrCode\Encoder\Mode;

it('exposes the correct mode-indicator bits', function (): void {
    expect(Mode::Numeric->indicator())->toBe(0b0001);
    expect(Mode::Alphanumeric->indicator())->toBe(0b0010);
    expect(Mode::Byte->indicator())->toBe(0b0100);
    expect(Mode::Kanji->indicator())->toBe(0b1000);
});

it('returns the right character-count indicator widths for V1..9 (Table 3)', function (): void {
    foreach ([1, 5, 9] as $v) {
        expect(Mode::Numeric->characterCountIndicatorBits($v))->toBe(10);
        expect(Mode::Alphanumeric->characterCountIndicatorBits($v))->toBe(9);
        expect(Mode::Byte->characterCountIndicatorBits($v))->toBe(8);
        expect(Mode::Kanji->characterCountIndicatorBits($v))->toBe(8);
    }
});

it('returns the right character-count indicator widths for V10..26', function (): void {
    foreach ([10, 20, 26] as $v) {
        expect(Mode::Numeric->characterCountIndicatorBits($v))->toBe(12);
        expect(Mode::Alphanumeric->characterCountIndicatorBits($v))->toBe(11);
        expect(Mode::Byte->characterCountIndicatorBits($v))->toBe(16);
        expect(Mode::Kanji->characterCountIndicatorBits($v))->toBe(10);
    }
});

it('returns the right character-count indicator widths for V27..40', function (): void {
    foreach ([27, 35, 40] as $v) {
        expect(Mode::Numeric->characterCountIndicatorBits($v))->toBe(14);
        expect(Mode::Alphanumeric->characterCountIndicatorBits($v))->toBe(13);
        expect(Mode::Byte->characterCountIndicatorBits($v))->toBe(16);
        expect(Mode::Kanji->characterCountIndicatorBits($v))->toBe(12);
    }
});

it('rejects out-of-range versions', function (): void {
    expect(fn () => Mode::Byte->characterCountIndicatorBits(0))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => Mode::Byte->characterCountIndicatorBits(41))
        ->toThrow(InvalidArgumentException::class);
});
