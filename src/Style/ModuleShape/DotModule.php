<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\ModuleShape;

/**
 * Filled circle inscribed in the 1×1 module. Circle center is at
 * (x + 0.5, y + 0.5) with radius 0.5. Drawn as two SVG arcs.
 */
final class DotModule implements ModuleShape
{
    public function svgPath(int $x, int $y): string
    {
        // Start at top of the circle (x + 0.5, y), arc 180° down to (x + 0.5, y + 1),
        // then arc 180° back up to the start.
        $cx = $x + 0.5;

        return "M{$cx} {$y}a.5 .5 0 1 1 0 1a.5 .5 0 1 1 0 -1z";
    }

    public function shapeRendering(): string
    {
        return 'geometricPrecision';
    }
}
