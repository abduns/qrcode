<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\ModuleShape;

/**
 * Smooth, neighbour-aware module: corners are rounded only when both
 * adjacent neighbours are absent. The corner radius is half the module
 * width so:
 *
 *   - An isolated module (no neighbours)  → renders as a full circle.
 *   - A 2-module run                       → pill / stadium shape.
 *   - An L-corner                          → 3 corners square, 1 rounded.
 *   - A solid block of 4+                  → plain square cells throughout.
 *
 * The four-point edge-midpoint walk produces clean joins: adjacent modules
 * with one rounded and one square corner touch at the edge midpoint with no
 * overlap or gap.
 */
final class RoundedModule implements ModuleShape
{
    public function svgPath(int $x, int $y, ModuleNeighbours $neighbours): string
    {
        $r = 0.5;

        $roundedTL = ! $neighbours->top && ! $neighbours->left;
        $roundedTR = ! $neighbours->top && ! $neighbours->right;
        $roundedBR = ! $neighbours->bottom && ! $neighbours->right;
        $roundedBL = ! $neighbours->bottom && ! $neighbours->left;

        // 4 edge midpoints we walk through, clockwise from top.
        $tx = $x + $r;
        $rx = $x + 1;
        $ry = $y + $r;
        $bx = $x + $r;
        $by = $y + 1;
        $ly = $y + $r;

        $path = "M{$tx} {$y}";

        // T → R: either an arc through the TR corner or two lines via (x+1, y).
        $path .= $roundedTR
            ? "a{$r} {$r} 0 0 1 {$r} {$r}"
            : "L{$rx} {$y}L{$rx} {$ry}";

        // R → B: arc through BR or lines via (x+1, y+1).
        $path .= $roundedBR
            ? "a{$r} {$r} 0 0 1 -{$r} {$r}"
            : "L{$rx} {$by}L{$bx} {$by}";

        // B → L: arc through BL or lines via (x, y+1).
        $path .= $roundedBL
            ? "a{$r} {$r} 0 0 1 -{$r} -{$r}"
            : "L{$x} {$by}L{$x} {$ly}";

        // L → T: arc through TL or lines via (x, y).
        $path .= $roundedTL
            ? "a{$r} {$r} 0 0 1 {$r} -{$r}"
            : "L{$x} {$y}L{$tx} {$y}";

        return $path.'z';
    }

    public function shapeRendering(): string
    {
        return 'geometricPrecision';
    }
}
