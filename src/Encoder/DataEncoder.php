<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Exception\DataTooLongException;
use Dunn\QrCode\Tables\CapacityTable;

/**
 * Orchestrates the data-encoding pipeline:
 *
 *   1. Auto-detect mode (or use supplied $forceMode).
 *   2. Auto-select smallest version that fits at the chosen ECC level.
 *   3. Append: mode indicator (4 bits), char-count indicator, payload.
 *   4. Append a terminator of up to 4 zero bits.
 *   5. Pad the bit stream to a byte boundary with zeros.
 *   6. Pad to the (version, ECC) data capacity with alternating 0xEC, 0x11.
 */
final class DataEncoder
{
    public function __construct(
        private readonly ModeDetector $detector = new ModeDetector(),
        private readonly NumericEncoder $numeric = new NumericEncoder(),
        private readonly AlphanumericEncoder $alphanumeric = new AlphanumericEncoder(),
        private readonly ByteEncoder $byte = new ByteEncoder(),
        private readonly VersionSelector $selector = new VersionSelector(),
    ) {
    }

    public function encode(
        string $data,
        EccLevel $ecc,
        ?int $forceVersion = null,
        ?Mode $forceMode = null,
    ): EncodedData {
        $mode = $forceMode ?? $this->detector->detect($data);
        $version = $forceVersion ?? $this->selector->selectVersion($data, $mode, $ecc);

        $capacityBits = CapacityTable::dataCapacityBits($version, $ecc);
        $headerBits = 4 + $mode->characterCountIndicatorBits($version);
        $needed = $headerBits + $this->selector->payloadBits($mode, $data);

        if ($needed > $capacityBits) {
            throw new DataTooLongException(
                "Input requires {$needed} bits but version {$version} ECC {$ecc->value} holds only {$capacityBits}."
            );
        }

        $buf = new BitBuffer();
        $buf->appendBits($mode->indicator(), 4);
        $buf->appendBits(strlen($data), $mode->characterCountIndicatorBits($version));

        match ($mode) {
            Mode::Numeric => $this->numeric->encode($data, $buf),
            Mode::Alphanumeric => $this->alphanumeric->encode($data, $buf),
            Mode::Byte => $this->byte->encode($data, $buf),
            Mode::Kanji => throw new \InvalidArgumentException('Kanji mode is not supported in v1.'),
        };

        // Terminator: up to 4 zero bits (truncated if we'd exceed capacity).
        $remainingBits = $capacityBits - $buf->size();
        $terminatorBits = min(4, $remainingBits);
        if ($terminatorBits > 0) {
            $buf->appendBits(0, $terminatorBits);
        }

        // Pad to a byte boundary.
        $padToByte = (8 - ($buf->size() % 8)) % 8;
        if ($padToByte > 0) {
            $buf->appendBits(0, $padToByte);
        }

        // Pad bytes alternating 0xEC, 0x11.
        $bytes = $buf->toBytes();
        $totalBytes = intdiv($capacityBits, 8);
        $padPattern = [0xEC, 0x11];
        $padIdx = 0;
        while (count($bytes) < $totalBytes) {
            $bytes[] = $padPattern[$padIdx];
            $padIdx ^= 1;
        }

        return new EncodedData($version, $ecc, $mode, $bytes);
    }
}
