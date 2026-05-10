<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Default outer ring: a 7×7 square with a 5×5 square hole.
 * Renders cleanly with `fill-rule="evenodd"`.
 */
final class SquareEyeOuter implements EyeOuter
{
    public function svgPath(int $x, int $y): string
    {
        return sprintf(
            'M%d %dh7v7h-7zM%d %dh5v5h-5z',
            $x,
            $y,
            $x + 1,
            $y + 1,
        );
    }

    public function shapeRendering(): string
    {
        return 'crispEdges';
    }
}
