<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Concentric-circle finder pattern: an outer dark ring (radii 3.5/2.5)
 * and an inner dark dot (radius 1.5), all centered in the 7×7 area.
 *
 * Modern QR decoders are tolerant of finder-pattern variations so long as
 * the 1:1:3:1:1 dark/light ratio is preserved on horizontal and vertical
 * scans through the center; this circular variant scans cleanly.
 */
final class CircleEye implements EyeStyle
{
    public function svgPath(int $x, int $y): string
    {
        $cy = $y + 3.5;

        // Outer circle (radius 3.5) — drawn as two semicircle arcs from leftmost point.
        $outer = sprintf(
            'M%s %sa3.5 3.5 0 1 0 7 0a3.5 3.5 0 1 0 -7 0z',
            self::fmt($x),
            self::fmt($cy),
        );

        // Hole: inner light ring at radius 2.5.
        $hole = sprintf(
            'M%s %sa2.5 2.5 0 1 0 5 0a2.5 2.5 0 1 0 -5 0z',
            self::fmt($x + 1),
            self::fmt($cy),
        );

        // Inner dark dot at radius 1.5.
        $dot = sprintf(
            'M%s %sa1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0z',
            self::fmt($x + 2),
            self::fmt($cy),
        );

        return $outer.$hole.$dot;
    }

    public function shapeRendering(): string
    {
        return 'geometricPrecision';
    }

    private static function fmt(float $v): string
    {
        // Compact float printing: drop trailing .0 for ints, keep .5 for halves.
        $s = (string) $v;

        return rtrim(rtrim($s, '0'), '.') ?: '0';
    }
}
