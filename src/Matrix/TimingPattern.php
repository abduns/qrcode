<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

/**
 * Alternating dark/light modules along row 6 and column 6, between the
 * finders' separators. Always starts and ends with a dark module.
 */
final class TimingPattern
{
    public function placeOn(Matrix $matrix): void
    {
        $size = $matrix->size();
        for ($i = 8; $i <= $size - 9; $i++) {
            $isDark = $i % 2 === 0;
            $matrix->setFunction(6, $i, $isDark);
            $matrix->setFunction($i, 6, $isDark);
        }
    }
}
