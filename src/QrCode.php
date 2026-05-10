<?php

declare(strict_types=1);

namespace Dunn\QrCode;

use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\Mask\MaskPattern;
use Dunn\QrCode\Matrix\Matrix;

/**
 * Immutable result of the QR Code generation pipeline. Holds the masked
 * matrix and the metadata renderers need.
 *
 * Use {@see QrCode::create()} to start building one:
 *
 *     $qr = QrCode::create('https://example.com')
 *         ->errorCorrection(EccLevel::Quartile)
 *         ->build();
 */
final readonly class QrCode
{
    public function __construct(
        public Matrix $matrix,
        public int $version,
        public EccLevel $eccLevel,
        public Mode $mode,
        public MaskPattern $maskPattern,
    ) {
    }

    public static function create(string $data): Builder
    {
        return new Builder($data);
    }

    public function size(): int
    {
        return $this->matrix->size();
    }
}
