<?php

declare(strict_types=1);

namespace Dunn\QrCode\Tables;

/**
 * Block layout for one (version, ECC level) combination. ISO/IEC 18004 Table 9.
 *
 * QR data is split into one or two "groups" of equal-sized blocks: group 2
 * blocks each carry exactly one more data codeword than group 1 blocks. Each
 * block is independently Reed–Solomon-encoded with the same number of ECC
 * codewords ({@see $eccPerBlock}).
 */
final readonly class EccBlockInfo
{
    public function __construct(
        public int $eccPerBlock,
        public int $group1Blocks,
        public int $group1DataPerBlock,
        public int $group2Blocks,
        public int $group2DataPerBlock,
    ) {
    }

    public function totalDataCodewords(): int
    {
        return $this->group1Blocks * $this->group1DataPerBlock
            + $this->group2Blocks * $this->group2DataPerBlock;
    }

    public function totalBlocks(): int
    {
        return $this->group1Blocks + $this->group2Blocks;
    }

    public function maxDataPerBlock(): int
    {
        return $this->group2Blocks > 0 ? $this->group2DataPerBlock : $this->group1DataPerBlock;
    }
}
