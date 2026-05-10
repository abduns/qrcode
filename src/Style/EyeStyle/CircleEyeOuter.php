<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Circular outer ring: a circle of radius 3.5 with a 2.5-radius hole,
 * centered in the 7×7 finder area.
 */
final class CircleEyeOuter implements EyeOuter
{
    public function svgPath(int $x, int $y): string
    {
        $cy = $y + 3.5;

        $outer = sprintf(
            'M%s %sa3.5 3.5 0 1 0 7 0a3.5 3.5 0 1 0 -7 0z',
            self::fmt($x),
            self::fmt($cy),
        );
        $hole = sprintf(
            'M%s %sa2.5 2.5 0 1 0 5 0a2.5 2.5 0 1 0 -5 0z',
            self::fmt($x + 1),
            self::fmt($cy),
        );

        return $outer.$hole;
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
