<?php

declare(strict_types=1);

use Dunn\QrCode\Tables\AlignmentPositions;

it('returns an empty list for V1', function (): void {
    expect(AlignmentPositions::forVersion(1))->toBe([]);
});

it('returns the canonical V2 positions [6, 18]', function (): void {
    expect(AlignmentPositions::forVersion(2))->toBe([6, 18]);
});

it('returns the canonical V7 positions [6, 22, 38]', function (): void {
    expect(AlignmentPositions::forVersion(7))->toBe([6, 22, 38]);
});

it('returns the canonical V40 positions (7 entries)', function (): void {
    expect(AlignmentPositions::forVersion(40))->toBe([6, 30, 58, 86, 114, 142, 170]);
});

it('rejects out-of-range versions', function (): void {
    expect(fn () => AlignmentPositions::forVersion(0))->toThrow(InvalidArgumentException::class);
    expect(fn () => AlignmentPositions::forVersion(41))->toThrow(InvalidArgumentException::class);
});

it('always starts each non-empty list with 6', function (): void {
    for ($v = 2; $v <= 40; $v++) {
        $positions = AlignmentPositions::forVersion($v);
        expect($positions[0])->toBe(6, "V{$v}");
    }
});
