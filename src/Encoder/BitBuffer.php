<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use InvalidArgumentException;

/**
 * Append-only bit buffer. Bits are stored MSB-first per appended value.
 * {@see toBytes()} pads any trailing partial byte with zeros.
 */
final class BitBuffer
{
    /** @var list<int> 0/1 values, MSB-first within each appended chunk */
    private array $bits = [];

    public function appendBits(int $value, int $bitCount): void
    {
        if ($bitCount < 0 || $bitCount > 31) {
            throw new InvalidArgumentException("bitCount must be in 0..31, got {$bitCount}");
        }
        if ($bitCount === 0) {
            return;
        }
        if ($value < 0 || ($value >> $bitCount) !== 0) {
            throw new InvalidArgumentException("value {$value} does not fit in {$bitCount} bits");
        }

        for ($i = $bitCount - 1; $i >= 0; $i--) {
            $this->bits[] = ($value >> $i) & 1;
        }
    }

    public function size(): int
    {
        return count($this->bits);
    }

    /** @return list<int> */
    public function getBits(): array
    {
        return $this->bits;
    }

    /**
     * Convert to a byte array, zero-padding any trailing partial byte.
     *
     * @return list<int>
     */
    public function toBytes(): array
    {
        $bytes = [];
        $count = count($this->bits);
        $byteCount = intdiv($count + 7, 8);

        for ($b = 0; $b < $byteCount; $b++) {
            $byte = 0;
            for ($k = 0; $k < 8; $k++) {
                $bitIndex = $b * 8 + $k;
                $bit = $bitIndex < $count ? $this->bits[$bitIndex] : 0;
                $byte = ($byte << 1) | $bit;
            }
            $bytes[] = $byte;
        }

        return $bytes;
    }
}
