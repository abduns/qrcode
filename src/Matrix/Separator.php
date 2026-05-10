<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

/**
 * 1-module light separator on the inside edges of each finder pattern.
 */
final class Separator
{
    public function placeOn(Matrix $matrix): void
    {
        $size = $matrix->size();

        // Top-left finder: row 7 cols 0..7, col 7 rows 0..6.
        for ($i = 0; $i < 8; $i++) {
            $matrix->setFunction(7, $i, false);
        }
        for ($i = 0; $i < 7; $i++) {
            $matrix->setFunction($i, 7, false);
        }

        // Top-right finder: row 7 cols size-8..size-1, col size-8 rows 0..6.
        for ($i = 0; $i < 8; $i++) {
            $matrix->setFunction(7, $size - 8 + $i, false);
        }
        for ($i = 0; $i < 7; $i++) {
            $matrix->setFunction($i, $size - 8, false);
        }

        // Bottom-left finder: row size-8 cols 0..7, col 7 rows size-7..size-1.
        for ($i = 0; $i < 8; $i++) {
            $matrix->setFunction($size - 8, $i, false);
        }
        for ($i = 0; $i < 7; $i++) {
            $matrix->setFunction($size - 7 + $i, 7, false);
        }
    }
}
