<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Default inner pupil: a 3×3 square centered in the 7×7 finder.
 */
final class SquareEyeInner implements EyeInner
{
    public function svgPath(int $x, int $y): string
    {
        return sprintf('M%d %dh3v3h-3z', $x + 2, $y + 2);
    }

    public function shapeRendering(): string
    {
        return 'crispEdges';
    }
}
