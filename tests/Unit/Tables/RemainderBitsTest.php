<?php

declare(strict_types=1);

use Dunn\QrCode\Tables\RemainderBits;

it('returns 0 for V1, 7, 35, 40', function (): void {
    expect(RemainderBits::forVersion(1))->toBe(0);
    expect(RemainderBits::forVersion(7))->toBe(0);
    expect(RemainderBits::forVersion(35))->toBe(0);
    expect(RemainderBits::forVersion(40))->toBe(0);
});

it('returns 7 for V2..V6', function (): void {
    foreach ([2, 3, 4, 5, 6] as $v) {
        expect(RemainderBits::forVersion($v))->toBe(7, "V{$v}");
    }
});

it('returns 3 for V14..V20 and V28..V34', function (): void {
    foreach ([14, 17, 20, 28, 31, 34] as $v) {
        expect(RemainderBits::forVersion($v))->toBe(3, "V{$v}");
    }
});

it('returns 4 for V21..V27', function (): void {
    foreach ([21, 24, 27] as $v) {
        expect(RemainderBits::forVersion($v))->toBe(4, "V{$v}");
    }
});

it('rejects out-of-range versions', function (): void {
    expect(fn () => RemainderBits::forVersion(0))->toThrow(InvalidArgumentException::class);
    expect(fn () => RemainderBits::forVersion(41))->toThrow(InvalidArgumentException::class);
});
