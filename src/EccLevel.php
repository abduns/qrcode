<?php

declare(strict_types=1);

namespace Dunn\QrCode;

/**
 * QR Code error correction level. Trades data capacity for damage tolerance.
 *
 *  - Low      ~7%  recoverable
 *  - Medium   ~15% recoverable
 *  - Quartile ~25% recoverable
 *  - High     ~30% recoverable
 *
 * Per ISO/IEC 18004 the format-info bit pattern is non-monotonic, hence the
 * unusual values returned by {@see formatBits()}.
 */
enum EccLevel: string
{
    case Low = 'L';
    case Medium = 'M';
    case Quartile = 'Q';
    case High = 'H';

    /**
     * 2-bit format-info encoding (ISO/IEC 18004 Table 12).
     */
    public function formatBits(): int
    {
        return match ($this) {
            self::Low => 0b01,
            self::Medium => 0b00,
            self::Quartile => 0b11,
            self::High => 0b10,
        };
    }
}
