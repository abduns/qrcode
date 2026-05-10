<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Circular inner pupil: a filled circle of radius 1.5 centered in the
 * 7×7 finder area.
 */
final class CircleEyeInner implements EyeInner
{
    public function svgPath(int $x, int $y): string
    {
        $cy = $y + 3.5;

        return sprintf(
            'M%s %sa1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0z',
            self::fmt($x + 2),
            self::fmt($cy),
        );
    }

    public function shapeRendering(): string
    {
        return 'geometricPrecision';
    }

    private static function fmt(float $v): string
    {
        $s = (string) $v;

        return rtrim(rtrim($s, '0'), '.') ?: '0';
    }
}
