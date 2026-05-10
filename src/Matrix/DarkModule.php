<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

use InvalidArgumentException;

/**
 * Single always-dark module at (4 * version + 9, 8). ISO/IEC 18004 §8.9.
 */
final class DarkModule
{
    public function placeOn(Matrix $matrix, int $version): void
    {
        if ($version < 1 || $version > 40) {
            throw new InvalidArgumentException("Version must be in 1..40, got {$version}");
        }
        $matrix->setFunction(4 * $version + 9, 8, true);
    }
}
