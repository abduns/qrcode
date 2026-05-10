<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Rounded inner pupil: a 3×3 rounded square at the centre of the 7×7
 * finder area. Radius 0.5 module — matches the visual weight of
 * RoundedModule data dots.
 */
final class RoundedEyeInner implements EyeInner
{
    public function svgPath(int $x, int $y): string
    {
        return sprintf(
            'M%s %sh2a.5 .5 0 0 1 .5 .5v2a.5 .5 0 0 1 -.5 .5h-2a.5 .5 0 0 1 -.5 -.5v-2a.5 .5 0 0 1 .5 -.5z',
            self::fmt($x + 2.5),
            self::fmt($y + 2),
        );
    }

    public function shapeRendering(): string
    {
        return 'geometricPrecision';
    }

    private static function fmt(float $v): string
    {
        if ($v === (float) (int) $v) {
            return (string) (int) $v;
        }
        $s = sprintf('%.3f', $v);

        return rtrim(rtrim($s, '0'), '.');
    }
}
