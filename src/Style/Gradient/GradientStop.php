<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\Gradient;

use Dunn\QrCode\Style\Color;
use InvalidArgumentException;

/**
 * One colour stop along a gradient's run from offset 0 to offset 1.
 */
final readonly class GradientStop
{
    public function __construct(
        public float $offset,
        public Color $color,
    ) {
        if ($offset < 0.0 || $offset > 1.0) {
            throw new InvalidArgumentException("GradientStop offset must be 0..1; got {$offset}");
        }
    }
}
