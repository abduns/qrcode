<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

/**
 * Byte-mode encoder. Each byte of the input becomes 8 bits in the buffer.
 *
 * Per ISO/IEC 18004 the default byte interpretation is ISO-8859-1; any UTF-8
 * payload is also accepted as raw bytes — most modern decoders treat
 * non-Latin-1 byte sequences as UTF-8.
 */
final class ByteEncoder
{
    public function encode(string $data, BitBuffer $buf): void
    {
        for ($i = 0, $n = strlen($data); $i < $n; $i++) {
            $buf->appendBits(ord($data[$i]), 8);
        }
    }
}
