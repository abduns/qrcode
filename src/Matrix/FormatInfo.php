<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

use Dunn\QrCode\EccLevel;
use InvalidArgumentException;

/**
 * Format-information region: 15 bits encoding (ECC level, mask pattern) +
 * BCH(15,5) error correction, XOR-masked with 0x5412. Placed in two
 * locations for redundancy:
 *
 *   - Around the top-left finder
 *   - Split between the top-right finder and the bottom-left finder
 *
 * The actual values depend on the chosen mask pattern (selected by the
 * masker), so {@see reserve()} is used during matrix construction and
 * {@see place()} is called once the mask has been chosen.
 */
final class FormatInfo
{
    /** ISO/IEC 18004 §8.9: BCH(15,5) generator polynomial */
    private const GENERATOR = 0b10100110111;

    /** ISO/IEC 18004 §8.9: format-info mask pattern */
    private const MASK = 0b101010000010010;

    public function reserve(Matrix $matrix): void
    {
        $size = $matrix->size();

        // Around top-left finder: row 8 cols 0..8 (skipping col 6 = timing),
        // col 8 rows 0..8 (skipping row 6 = timing).
        for ($c = 0; $c <= 8; $c++) {
            if ($c !== 6) {
                $matrix->reserve(8, $c);
            }
        }
        for ($r = 0; $r <= 8; $r++) {
            if ($r !== 6) {
                $matrix->reserve($r, 8);
            }
        }

        // Top-right: row 8, cols size-8..size-1.
        for ($c = $size - 8; $c < $size; $c++) {
            $matrix->reserve(8, $c);
        }

        // Bottom-left: col 8, rows size-7..size-1.
        for ($r = $size - 7; $r < $size; $r++) {
            $matrix->reserve($r, 8);
        }
    }

    public function place(Matrix $matrix, EccLevel $ecc, int $maskPattern): void
    {
        if ($maskPattern < 0 || $maskPattern > 7) {
            throw new InvalidArgumentException("maskPattern must be in 0..7, got {$maskPattern}");
        }

        $bits = self::computeFormatBits($ecc->formatBits(), $maskPattern);
        $size = $matrix->size();

        // Bit i (0 = LSB) of bits goes to two physical positions per ISO 18004 §8.9.
        for ($i = 0; $i < 15; $i++) {
            $bit = (($bits >> $i) & 1) === 1;

            // Around the top-left finder.
            [$r1, $c1] = self::topLeftPosition($i);
            $matrix->set($r1, $c1, $bit);

            // Split between top-right and bottom-left finders.
            [$r2, $c2] = self::splitPosition($i, $size);
            $matrix->set($r2, $c2, $bit);
        }
    }

    /**
     * BCH(15,5) encode + XOR mask. Returns a 15-bit value.
     */
    public static function computeFormatBits(int $eccBits, int $maskPattern): int
    {
        $data = ($eccBits << 3) | $maskPattern; // 5 bits

        $remainder = $data << 10;
        for ($i = 4; $i >= 0; $i--) {
            if ((($remainder >> ($i + 10)) & 1) === 1) {
                $remainder ^= self::GENERATOR << $i;
            }
        }

        $codeword = ($data << 10) | ($remainder & 0x3FF);

        return $codeword ^ self::MASK;
    }

    /**
     * @return array{int, int} (row, col) for bit $i in the top-left placement.
     */
    private static function topLeftPosition(int $i): array
    {
        // Bit 0..5: row 8, col 0..5.
        // Bit 6:    row 8, col 7 (col 6 skipped: timing pattern).
        // Bit 7:    row 8, col 8.
        // Bit 8:    row 7, col 8 (row 6 skipped: timing).
        // Bit 9..14: rows 5..0, col 8.
        if ($i < 6) {
            return [8, $i];
        }
        if ($i === 6) {
            return [8, 7];
        }
        if ($i === 7) {
            return [8, 8];
        }
        if ($i === 8) {
            return [7, 8];
        }

        return [14 - $i, 8]; // i=9 -> row 5, ..., i=14 -> row 0.
    }

    /**
     * @return array{int, int} (row, col) for bit $i in the redundant placement.
     */
    private static function splitPosition(int $i, int $size): array
    {
        // Bit 0..6:   col 8, rows size-1..size-7.
        // Bit 7..14:  row 8, cols size-8..size-1.
        if ($i < 7) {
            return [$size - 1 - $i, 8];
        }

        return [8, $size - 15 + $i]; // i=7 -> col size-8, ..., i=14 -> col size-1.
    }
}
