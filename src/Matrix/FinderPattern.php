<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

/**
 * Three 7×7 finder patterns at the top-left, top-right, and bottom-left
 * corners. Each is a dark outer 7×7 ring, a light inner 5×5 ring,
 * and a dark inner 3×3 square.
 */
final class FinderPattern
{
    public function placeOn(Matrix $matrix): void
    {
        $size = $matrix->size();
        $this->placeAt($matrix, 0, 0);
        $this->placeAt($matrix, 0, $size - 7);
        $this->placeAt($matrix, $size - 7, 0);
    }

    private function placeAt(Matrix $matrix, int $rowOrigin, int $colOrigin): void
    {
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                $isOuterRing = $r === 0 || $r === 6 || $c === 0 || $c === 6;
                $isInnerSquare = $r >= 2 && $r <= 4 && $c >= 2 && $c <= 4;
                $isDark = $isOuterRing || $isInnerSquare;

                $matrix->setFunction($rowOrigin + $r, $colOrigin + $c, $isDark);
            }
        }
    }
}
