<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\ErrorCorrection\ReedSolomon;
use Dunn\QrCode\Tables\EccTable;
use InvalidArgumentException;

/**
 * Splits the data codeword stream into RS blocks per the (version, ECC) layout,
 * encodes each block, then interleaves bytes column-major: first byte of every
 * data block, second byte of every data block, … then likewise for ECC blocks.
 *
 * When group 2 blocks carry one more data codeword than group 1 blocks, the
 * shorter group 1 blocks are skipped over in the final column.
 */
final class BlockInterleaver
{
    public function __construct(private readonly ReedSolomon $rs)
    {
    }

    /**
     * @param list<int> $dataCodewords Full data stream from {@see DataEncoder}.
     * @return list<int> Interleaved (data, then ECC) codeword stream.
     */
    public function interleave(array $dataCodewords, int $version, EccLevel $ecc): array
    {
        $info = EccTable::lookup($version, $ecc);

        if (count($dataCodewords) !== $info->totalDataCodewords()) {
            throw new InvalidArgumentException(sprintf(
                'Expected %d data codewords for V%d-%s; got %d.',
                $info->totalDataCodewords(),
                $version,
                $ecc->value,
                count($dataCodewords),
            ));
        }

        // Split into blocks.
        /** @var list<list<int>> $dataBlocks */
        $dataBlocks = [];
        $offset = 0;
        for ($i = 0; $i < $info->group1Blocks; $i++) {
            $dataBlocks[] = array_slice($dataCodewords, $offset, $info->group1DataPerBlock);
            $offset += $info->group1DataPerBlock;
        }
        for ($i = 0; $i < $info->group2Blocks; $i++) {
            $dataBlocks[] = array_slice($dataCodewords, $offset, $info->group2DataPerBlock);
            $offset += $info->group2DataPerBlock;
        }

        // RS-encode each block.
        /** @var list<list<int>> $eccBlocks */
        $eccBlocks = [];
        foreach ($dataBlocks as $block) {
            $eccBlocks[] = $this->rs->encode($block, $info->eccPerBlock);
        }

        // Interleave data: column-major across all blocks; skip group 1 in the
        // final column when group 2 blocks are longer.
        /** @var list<int> $result */
        $result = [];
        $maxData = $info->maxDataPerBlock();
        for ($col = 0; $col < $maxData; $col++) {
            foreach ($dataBlocks as $block) {
                if ($col < count($block)) {
                    $result[] = $block[$col];
                }
            }
        }

        // Interleave ECC: every ECC block has the same length, so no skipping.
        for ($col = 0; $col < $info->eccPerBlock; $col++) {
            foreach ($eccBlocks as $block) {
                $result[] = $block[$col];
            }
        }

        return $result;
    }
}
