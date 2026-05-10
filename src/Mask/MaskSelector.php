<?php

declare(strict_types=1);

namespace Dunn\QrCode\Mask;

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Matrix\FormatInfo;
use Dunn\QrCode\Matrix\Matrix;

/**
 * Try every mask pattern on a copy of the unmasked matrix, score each,
 * return the lowest-penalty pair (matrix, mask).
 *
 * The format-info bits depend on the chosen mask, so each candidate also
 * has its format-info written before scoring.
 */
final class MaskSelector
{
    public function __construct(
        private readonly PenaltyEvaluator $evaluator = new PenaltyEvaluator(),
    ) {
    }

    /**
     * @return array{0: Matrix, 1: MaskPattern}
     */
    public function selectAndApply(Matrix $matrix, EccLevel $ecc): array
    {
        $bestPenalty = PHP_INT_MAX;
        $bestMatrix = $matrix;
        $bestMask = MaskPattern::Pattern0;
        $formatInfo = new FormatInfo();

        foreach (MaskPattern::cases() as $mask) {
            $candidate = clone $matrix;
            $mask->applyTo($candidate);
            $formatInfo->place($candidate, $ecc, $mask->value);

            $penalty = $this->evaluator->evaluate($candidate);
            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $bestMatrix = $candidate;
                $bestMask = $mask;
            }
        }

        return [$bestMatrix, $bestMask];
    }
}
