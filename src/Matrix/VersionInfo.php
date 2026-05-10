<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

use InvalidArgumentException;

/**
 * Version-information region (V7+ only): 18 bits encoding the version
 * number with BCH(18,6) error correction, placed in two 6×3 / 3×6 blocks:
 *
 *   - Top-right: rows 0..5, cols size-11..size-9
 *   - Bottom-left: rows size-11..size-9, cols 0..5
 *
 * The values depend only on the version, so this class places them
 * directly during matrix construction.
 */
final class VersionInfo
{
    /** ISO/IEC 18004 §8.10: BCH(18,6) generator polynomial */
    private const GENERATOR = 0x1F25;

    public function placeOn(Matrix $matrix, int $version): void
    {
        if ($version < 7) {
            return; // V1..V6 have no version info region
        }
        if ($version > 40) {
            throw new InvalidArgumentException("Version must be in 7..40, got {$version}");
        }

        $bits = self::computeVersionBits($version);
        $size = $matrix->size();

        for ($i = 0; $i < 18; $i++) {
            $isDark = (($bits >> $i) & 1) === 1;
            $r = intdiv($i, 3);
            $c = $i % 3;

            // Top-right block.
            $matrix->setFunction($r, $size - 11 + $c, $isDark);
            // Bottom-left block (transposed).
            $matrix->setFunction($size - 11 + $c, $r, $isDark);
        }
    }

    /**
     * BCH(18,6) encode the version number into an 18-bit codeword.
     */
    public static function computeVersionBits(int $version): int
    {
        $remainder = $version << 12;
        for ($i = 5; $i >= 0; $i--) {
            if ((($remainder >> ($i + 12)) & 1) === 1) {
                $remainder ^= self::GENERATOR << $i;
            }
        }

        return ($version << 12) | ($remainder & 0xFFF);
    }
}
