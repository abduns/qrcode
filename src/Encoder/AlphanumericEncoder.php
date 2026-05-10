<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use InvalidArgumentException;

/**
 * Alphanumeric-mode encoder. Pairs of characters → 11 bits each
 * (first*45 + second). A trailing odd character → 6 bits.
 */
final class AlphanumericEncoder
{
    public const CHARSET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

    public function encode(string $data, BitBuffer $buf): void
    {
        $codes = [];
        for ($i = 0, $n = strlen($data); $i < $n; $i++) {
            $idx = strpos(self::CHARSET, $data[$i]);
            if ($idx === false) {
                throw new InvalidArgumentException("Character '{$data[$i]}' is not in the alphanumeric charset.");
            }
            $codes[] = $idx;
        }

        $count = count($codes);
        for ($i = 0; $i + 1 < $count; $i += 2) {
            $buf->appendBits($codes[$i] * 45 + $codes[$i + 1], 11);
        }
        if ($count % 2 === 1) {
            $buf->appendBits($codes[$count - 1], 6);
        }
    }
}
