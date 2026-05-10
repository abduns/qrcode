<?php

declare(strict_types=1);

namespace Dunn\QrCode\Mask;

use Dunn\QrCode\Matrix\Matrix;

/**
 * The 4 mask-evaluation penalty rules from ISO/IEC 18004 §8.8.2.
 *
 *   N1: 5+ same-colour modules in a row/column → 3 + (run − 5) per run.
 *   N2: 2×2 same-colour blocks → 3 per block.
 *   N3: dark/light pattern matching DLDDDLD with 4-light border → 40 per match.
 *   N4: deviation from 50% dark coverage → 10 × floor(|%-50|/5).
 *
 * Every rule walks the full matrix. The total penalty is the sum.
 */
final class PenaltyEvaluator
{
    public function evaluate(Matrix $matrix): int
    {
        return $this->n1($matrix)
            + $this->n2($matrix)
            + $this->n3($matrix)
            + $this->n4($matrix);
    }

    public function n1(Matrix $matrix): int
    {
        $size = $matrix->size();
        $penalty = 0;

        // Rows
        for ($r = 0; $r < $size; $r++) {
            $runLen = 1;
            $runColor = $matrix->get($r, 0);
            for ($c = 1; $c < $size; $c++) {
                $color = $matrix->get($r, $c);
                if ($color === $runColor) {
                    $runLen++;
                } else {
                    if ($runLen >= 5) {
                        $penalty += 3 + ($runLen - 5);
                    }
                    $runColor = $color;
                    $runLen = 1;
                }
            }
            if ($runLen >= 5) {
                $penalty += 3 + ($runLen - 5);
            }
        }

        // Columns
        for ($c = 0; $c < $size; $c++) {
            $runLen = 1;
            $runColor = $matrix->get(0, $c);
            for ($r = 1; $r < $size; $r++) {
                $color = $matrix->get($r, $c);
                if ($color === $runColor) {
                    $runLen++;
                } else {
                    if ($runLen >= 5) {
                        $penalty += 3 + ($runLen - 5);
                    }
                    $runColor = $color;
                    $runLen = 1;
                }
            }
            if ($runLen >= 5) {
                $penalty += 3 + ($runLen - 5);
            }
        }

        return $penalty;
    }

    public function n2(Matrix $matrix): int
    {
        $size = $matrix->size();
        $penalty = 0;
        for ($r = 0; $r < $size - 1; $r++) {
            for ($c = 0; $c < $size - 1; $c++) {
                $tl = $matrix->get($r, $c);
                if ($matrix->get($r, $c + 1) === $tl
                    && $matrix->get($r + 1, $c) === $tl
                    && $matrix->get($r + 1, $c + 1) === $tl) {
                    $penalty += 3;
                }
            }
        }

        return $penalty;
    }

    public function n3(Matrix $matrix): int
    {
        $size = $matrix->size();
        $penalty = 0;

        $patternA = [true, false, true, true, true, false, true, false, false, false, false]; // DLDDDLD + 4L
        $patternB = [false, false, false, false, true, false, true, true, true, false, true]; // 4L + DLDDDLD

        // Rows
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c <= $size - 11; $c++) {
                $window = [];
                for ($i = 0; $i < 11; $i++) {
                    $window[] = $matrix->get($r, $c + $i);
                }
                if ($window === $patternA || $window === $patternB) {
                    $penalty += 40;
                }
            }
        }

        // Columns
        for ($c = 0; $c < $size; $c++) {
            for ($r = 0; $r <= $size - 11; $r++) {
                $window = [];
                for ($i = 0; $i < 11; $i++) {
                    $window[] = $matrix->get($r + $i, $c);
                }
                if ($window === $patternA || $window === $patternB) {
                    $penalty += 40;
                }
            }
        }

        return $penalty;
    }

    public function n4(Matrix $matrix): int
    {
        $size = $matrix->size();
        $total = $size * $size;
        $dark = 0;
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($matrix->get($r, $c)) {
                    $dark++;
                }
            }
        }

        // k = ceil(|20*dark - 10*total| / total) - 1
        // Penalty = max(0, k) * 10  (Nayuki's formulation, well-tested.)
        $deviation = abs(20 * $dark - 10 * $total);
        $k = intdiv($deviation + $total - 1, $total) - 1;

        return max(0, $k) * 10;
    }
}
