<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

use Dunn\QrCode\Tables\AlignmentPositions;

/**
 * 5×5 alignment patterns at every (row, col) cartesian-product position
 * defined for the version, except the three corners that overlap the
 * finder patterns (top-left, top-right, bottom-left of the position grid).
 *
 * Each pattern is a dark outer 5×5 ring, a light inner 3×3 ring,
 * and a single dark center.
 */
final class AlignmentPattern
{
    public function placeOn(Matrix $matrix, int $version): void
    {
        $positions = AlignmentPositions::forVersion($version);
        $n = count($positions);
        if ($n === 0) {
            return;
        }
        $last = $n - 1;

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $isFinderCorner = ($i === 0 && $j === 0)
                    || ($i === 0 && $j === $last)
                    || ($i === $last && $j === 0);
                if ($isFinderCorner) {
                    continue;
                }
                $this->placeAt($matrix, $positions[$i], $positions[$j]);
            }
        }
    }

    private function placeAt(Matrix $matrix, int $centerRow, int $centerCol): void
    {
        for ($dr = -2; $dr <= 2; $dr++) {
            for ($dc = -2; $dc <= 2; $dc++) {
                $distance = max(abs($dr), abs($dc));
                $isDark = $distance !== 1; // dark ring at distance 0 and 2; light at 1
                $matrix->setFunction($centerRow + $dr, $centerCol + $dc, $isDark);
            }
        }
    }
}
