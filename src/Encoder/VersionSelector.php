<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Exception\DataTooLongException;
use Dunn\QrCode\Tables\CapacityTable;
use InvalidArgumentException;

/**
 * Pick the smallest version that fits a given (data, mode, ECC level) input.
 *
 * The character-count indicator widens at the V10 and V27 boundaries, so each
 * version's required-bits calculation is recomputed independently.
 */
final class VersionSelector
{
    public function selectVersion(string $data, Mode $mode, EccLevel $ecc): int
    {
        $payloadBits = $this->payloadBits($mode, $data);

        for ($v = 1; $v <= 40; $v++) {
            $headerBits = 4 + $mode->characterCountIndicatorBits($v);
            $needed = $headerBits + $payloadBits;
            if ($needed <= CapacityTable::dataCapacityBits($v, $ecc)) {
                return $v;
            }
        }

        throw new DataTooLongException(
            'Input does not fit at any QR version (1..40) for ECC '.$ecc->value
        );
    }

    public function payloadBits(Mode $mode, string $data): int
    {
        $len = strlen($data);

        return match ($mode) {
            Mode::Numeric => intdiv($len, 3) * 10 + match ($len % 3) {
                0 => 0,
                1 => 4,
                2 => 7,
            },
            Mode::Alphanumeric => intdiv($len, 2) * 11 + ($len % 2) * 6,
            Mode::Byte => $len * 8,
            Mode::Kanji => throw new InvalidArgumentException('Kanji mode is not supported in v1.'),
        };
    }
}
