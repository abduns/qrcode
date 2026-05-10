<?php

declare(strict_types=1);

use Dunn\QrCode\Matrix\Matrix;
use Dunn\QrCode\Matrix\VersionInfo;

it('computes the V7 version-info codeword as 0x07C94 (Annex D)', function (): void {
    expect(VersionInfo::computeVersionBits(7))->toBe(0x07C94);
});

it('computes the V10 version-info codeword as 0x0A4D3', function (): void {
    expect(VersionInfo::computeVersionBits(10))->toBe(0x0A4D3);
});

it('computes the V40 version-info codeword as 0x28C69', function (): void {
    expect(VersionInfo::computeVersionBits(40))->toBe(0x28C69);
});

it('places nothing for V1..V6', function (): void {
    foreach ([1, 6] as $v) {
        $m = new Matrix($v);
        (new VersionInfo())->placeOn($m, $v);

        for ($r = 0; $r < $m->size(); $r++) {
            for ($c = 0; $c < $m->size(); $c++) {
                expect($m->isReserved($r, $c))->toBeFalse();
            }
        }
    }
});

it('places the version info into two 6×3 / 3×6 blocks for V7', function (): void {
    $m = new Matrix(7);
    (new VersionInfo())->placeOn($m, 7);

    $size = $m->size(); // 45

    // Each of the 18 cells in both blocks must be reserved.
    $reservedCount = 0;
    for ($r = 0; $r < $size; $r++) {
        for ($c = 0; $c < $size; $c++) {
            if ($m->isReserved($r, $c)) {
                $reservedCount++;
            }
        }
    }
    expect($reservedCount)->toBe(36); // 18 + 18

    // Top-right block at rows 0..5, cols size-11..size-9.
    for ($r = 0; $r < 6; $r++) {
        for ($c = $size - 11; $c < $size - 8; $c++) {
            expect($m->isReserved($r, $c))->toBeTrue("TR ({$r},{$c})");
        }
    }

    // Bottom-left block at rows size-11..size-9, cols 0..5.
    for ($r = $size - 11; $r < $size - 8; $r++) {
        for ($c = 0; $c < 6; $c++) {
            expect($m->isReserved($r, $c))->toBeTrue("BL ({$r},{$c})");
        }
    }
});
