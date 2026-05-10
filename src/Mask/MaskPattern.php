<?php

declare(strict_types=1);

namespace Dunn\QrCode\Mask;

use Dunn\QrCode\Matrix\Matrix;

/**
 * The 8 mask patterns from ISO/IEC 18004 §8.8.1. Each one defines a
 * {@see predicate()} that, when true for (row, col), inverts the
 * corresponding data module.
 *
 * Function-pattern modules (reserved cells) are never masked.
 */
enum MaskPattern: int
{
    case Pattern0 = 0;
    case Pattern1 = 1;
    case Pattern2 = 2;
    case Pattern3 = 3;
    case Pattern4 = 4;
    case Pattern5 = 5;
    case Pattern6 = 6;
    case Pattern7 = 7;

    public function predicate(int $row, int $col): bool
    {
        return match ($this) {
            self::Pattern0 => ($row + $col) % 2 === 0,
            self::Pattern1 => $row % 2 === 0,
            self::Pattern2 => $col % 3 === 0,
            self::Pattern3 => ($row + $col) % 3 === 0,
            self::Pattern4 => (intdiv($row, 2) + intdiv($col, 3)) % 2 === 0,
            self::Pattern5 => (($row * $col) % 2) + (($row * $col) % 3) === 0,
            self::Pattern6 => ((($row * $col) % 2) + (($row * $col) % 3)) % 2 === 0,
            self::Pattern7 => ((($row + $col) % 2) + (($row * $col) % 3)) % 2 === 0,
        };
    }

    /**
     * XOR this mask onto the data modules of $matrix in place. Reserved
     * cells (function patterns + format/version info) are left untouched.
     */
    public function applyTo(Matrix $matrix): void
    {
        $size = $matrix->size();
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (! $matrix->isReserved($r, $c) && $this->predicate($r, $c)) {
                    $matrix->set($r, $c, ! $matrix->get($r, $c));
                }
            }
        }
    }
}
