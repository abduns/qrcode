<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use InvalidArgumentException;

/**
 * Numeric-mode encoder. Groups input digits into runs of 3 (10 bits each),
 * with the trailing 2 digits → 7 bits and 1 digit → 4 bits.
 */
final class NumericEncoder
{
    public function encode(string $data, BitBuffer $buf): void
    {
        if ($data !== '' && preg_match('/^[0-9]+$/', $data) !== 1) {
            throw new InvalidArgumentException('Numeric mode requires digit characters only.');
        }

        $i = 0;
        $len = strlen($data);

        while ($i + 3 <= $len) {
            $buf->appendBits((int) substr($data, $i, 3), 10);
            $i += 3;
        }

        $remaining = $len - $i;
        if ($remaining === 2) {
            $buf->appendBits((int) substr($data, $i, 2), 7);
        } elseif ($remaining === 1) {
            $buf->appendBits((int) substr($data, $i, 1), 4);
        }
    }
}
