<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

use InvalidArgumentException;

/**
 * QR data-encoding mode. Indicator value (4 bits) is the enum's int value.
 *
 * Kanji is declared for completeness but not implemented in v1.
 */
enum Mode: int
{
    case Numeric = 0b0001;
    case Alphanumeric = 0b0010;
    case Byte = 0b0100;
    case Kanji = 0b1000;

    public function indicator(): int
    {
        return $this->value;
    }

    /**
     * Bit width of the character-count indicator for this mode at the given
     * version. ISO/IEC 18004 Table 3.
     */
    public function characterCountIndicatorBits(int $version): int
    {
        if ($version < 1 || $version > 40) {
            throw new InvalidArgumentException("Version must be in 1..40, got {$version}");
        }

        if ($version <= 9) {
            return match ($this) {
                self::Numeric => 10,
                self::Alphanumeric => 9,
                self::Byte => 8,
                self::Kanji => 8,
            };
        }

        if ($version <= 26) {
            return match ($this) {
                self::Numeric => 12,
                self::Alphanumeric => 11,
                self::Byte => 16,
                self::Kanji => 10,
            };
        }

        return match ($this) {
            self::Numeric => 14,
            self::Alphanumeric => 13,
            self::Byte => 16,
            self::Kanji => 12,
        };
    }
}
