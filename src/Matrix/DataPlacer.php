<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

use InvalidArgumentException;

/**
 * Walks the matrix in 2-column-wide vertical strips, alternating direction
 * upward / downward starting from the bottom-right corner, skipping column
 * 6 (timing pattern). Within each row of a strip, the right-hand column is
 * filled first. Reserved cells are skipped silently.
 *
 * Bits are consumed MSB-first per byte from the codeword stream; once the
 * stream is exhausted the remaining unreserved cells are filled with 0
 * ("remainder bits"), which the matrix already has by default.
 */
final class DataPlacer
{
    /**
     * @param list<int> $codewords Interleaved data + ECC byte stream.
     */
    public function place(Matrix $matrix, array $codewords): void
    {
        $size = $matrix->size();
        $totalBits = count($codewords) * 8;
        $bitIndex = 0;

        $upward = true;
        for ($col = $size - 1; $col > 0; $col -= 2) {
            if ($col === 6) {
                $col--;
            }

            for ($i = 0; $i < $size; $i++) {
                $row = $upward ? $size - 1 - $i : $i;

                for ($cOffset = 0; $cOffset < 2; $cOffset++) {
                    $c = $col - $cOffset;
                    if ($matrix->isReserved($row, $c)) {
                        continue;
                    }

                    if ($bitIndex < $totalBits) {
                        $byte = $codewords[$bitIndex >> 3];
                        $bit = (($byte >> (7 - ($bitIndex & 7))) & 1) === 1;
                        $matrix->set($row, $c, $bit);
                    }
                    // else: remainder bit — already 0 (light) by default.
                    $bitIndex++;
                }
            }

            $upward = ! $upward;
        }

        // Sanity: every unreserved cell got a bit (data + remainder).
        // Caller should size $codewords so that totalBits + remainder = unreserved count.
        if ($bitIndex < $totalBits) {
            throw new InvalidArgumentException(
                "Codeword stream too long: only {$bitIndex} bits placed, {$totalBits} needed."
            );
        }
    }
}
