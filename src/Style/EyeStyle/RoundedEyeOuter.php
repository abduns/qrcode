<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\EyeStyle;

/**
 * Rounded-square outer ring: a 7×7 rounded outer rect with a 5×5 rounded
 * hole. Pairs naturally with {@see \Dunn\QrCode\Style\ModuleShape\RoundedModule}.
 */
final class RoundedEyeOuter implements EyeOuter
{
    public function svgPath(int $x, int $y): string
    {
        return sprintf(
            'M%d %dh5a1 1 0 0 1 1 1v5a1 1 0 0 1 -1 1h-5a1 1 0 0 1 -1 -1v-5a1 1 0 0 1 1 -1z'
            .'M%d %dh3a1 1 0 0 1 1 1v3a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1z',
            $x + 1,
            $y,
            $x + 2,
            $y + 1,
        );
    }

    public function shapeRendering(): string
    {
        return 'geometricPrecision';
    }
}
