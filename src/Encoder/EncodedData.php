<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use Dunn\QrCode\EccLevel;

/**
 * Result of {@see DataEncoder::encode()}: the chosen version + mode plus the
 * full data-codeword stream (already padded out to the (version, ECC) data
 * capacity, ready for block-level RS encoding and interleaving).
 */
final readonly class EncodedData
{
    /** @param list<int> $codewords */
    public function __construct(
        public int $version,
        public EccLevel $eccLevel,
        public Mode $mode,
        public array $codewords,
    ) {
    }
}
