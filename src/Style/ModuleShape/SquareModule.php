<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\ModuleShape;

/**
 * The default 1×1 axis-aligned square module. Renders with crisp edges.
 */
final class SquareModule implements ModuleShape
{
    public function svgPath(int $x, int $y, ModuleNeighbours $neighbours): string
    {
        // SquareModule is context-free: $neighbours is ignored.
        return "M{$x} {$y}h1v1h-1z";
    }

    public function shapeRendering(): string
    {
        return 'crispEdges';
    }
}
