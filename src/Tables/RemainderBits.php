<?php

declare(strict_types=1);

namespace Dunn\QrCode\Tables;

use InvalidArgumentException;

/**
 * Per-version remainder bits appended after the interleaved data + ECC stream
 * fills the matrix. ISO/IEC 18004 Table 1, "Remainder bits" column.
 */
final class RemainderBits
{
    /** @var array<int, int> */
    private const TABLE = [
        1  => 0,
        2  => 7,  3  => 7,  4  => 7,  5  => 7,  6  => 7,
        7  => 0,  8  => 0,  9  => 0,  10 => 0,  11 => 0, 12 => 0, 13 => 0,
        14 => 3,  15 => 3,  16 => 3,  17 => 3,  18 => 3, 19 => 3, 20 => 3,
        21 => 4,  22 => 4,  23 => 4,  24 => 4,  25 => 4, 26 => 4, 27 => 4,
        28 => 3,  29 => 3,  30 => 3,  31 => 3,  32 => 3, 33 => 3, 34 => 3,
        35 => 0,  36 => 0,  37 => 0,  38 => 0,  39 => 0, 40 => 0,
    ];

    public static function forVersion(int $version): int
    {
        if ($version < 1 || $version > 40) {
            throw new InvalidArgumentException("Version must be in 1..40, got {$version}");
        }

        return self::TABLE[$version];
    }
}
