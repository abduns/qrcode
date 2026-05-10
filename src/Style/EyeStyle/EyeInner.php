<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Strategy for rendering the *inner* portion of a 7×7 finder pattern: the
 * central 3×3 dark area.
 *
 * (x, y) is the top-left corner of the full 7×7 finder area (NOT of the
 * inner 3×3) — implementations apply the +2 offset themselves. Return an
 * SVG path fragment for the central dark dot or square.
 */
interface EyeInner
{
    public function svgPath(int $x, int $y): string;

    public function shapeRendering(): string;
}
