<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * The default square finder pattern: 7×7 dark outer ring, 5×5 light inner
 * ring, 3×3 dark center. Rendered as three nested rectangles relying on
 * the `fill-rule="evenodd"` rule to alternate filled/hole/filled.
 */
final class SquareEye implements EyeStyle
{
    public function svgPath(int $x, int $y): string
    {
        return sprintf(
            'M%d %dh7v7h-7zM%d %dh5v5h-5zM%d %dh3v3h-3z',
            $x,
            $y,
            $x + 1,
            $y + 1,
            $x + 2,
            $y + 2,
        );
    }

    public function shapeRendering(): string
    {
        return 'crispEdges';
    }
}
