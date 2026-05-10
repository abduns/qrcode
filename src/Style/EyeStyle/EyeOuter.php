<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Strategy for rendering the *outer* portion of a 7×7 finder pattern: the
 * dark "ring" that surrounds the inner 3×3 center.
 *
 * (x, y) is the top-left corner of the full 7×7 finder area in margin-adjusted
 * matrix coordinates. Return an SVG path fragment describing the dark
 * portions only — typically the outer 7×7 shape minus an inner 5×5 hole,
 * using `fill-rule="evenodd"` to render the ring.
 */
interface EyeOuter
{
    public function svgPath(int $x, int $y): string;

    public function shapeRendering(): string;
}
