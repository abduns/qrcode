<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Strategy for rendering a 7×7 finder pattern (one of the three large
 * corners of a QR code). The renderer skips the 7×7 area when iterating
 * data modules and delegates to {@see svgPath()} for each finder.
 *
 * Implementations return an SVG path fragment (typically with multiple
 * subpaths and `fill-rule="evenodd"` to create the dark/light/dark
 * concentric structure).
 *
 * (x, y) is the top-left corner of the 7×7 area in margin-adjusted matrix
 * coordinates.
 */
interface EyeStyle
{
    public function svgPath(int $x, int $y): string;

    public function shapeRendering(): string;
}
