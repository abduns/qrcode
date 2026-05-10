<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

/**
 * Convenience orchestrator: applies all function patterns + format-info
 * reservation + data placement in spec order, returning a matrix that is
 * ready for masking.
 *
 * The format-info modules are reserved (so the data placer skips them) but
 * not yet written — the masker writes the final 15 bits once it has chosen
 * the lowest-penalty mask pattern.
 */
final class MatrixBuilder
{
    /**
     * @param list<int> $interleavedCodewords Result of {@see \Dunn\QrCode\Encoder\BlockInterleaver::interleave()}.
     */
    public function build(int $version, array $interleavedCodewords): Matrix
    {
        $matrix = new Matrix($version);

        (new FinderPattern())->placeOn($matrix);
        (new Separator())->placeOn($matrix);
        (new TimingPattern())->placeOn($matrix);
        (new DarkModule())->placeOn($matrix, $version);
        (new AlignmentPattern())->placeOn($matrix, $version);
        (new VersionInfo())->placeOn($matrix, $version);
        (new FormatInfo())->reserve($matrix);

        (new DataPlacer())->place($matrix, $interleavedCodewords);

        return $matrix;
    }
}
