<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\ModuleShape;

/**
 * Strategy for rendering a single dark data module as an SVG path fragment.
 *
 * Implementations are passed (x, y) — the top-left of a 1×1 module in the
 * QR matrix's coordinate space (already shifted by the renderer's margin) —
 * plus a {@see ModuleNeighbours} describing which of the four cardinal
 * neighbours are also dark. Neighbour-aware shapes (e.g. {@see RoundedModule})
 * use this to smooth runs; context-free shapes ({@see SquareModule},
 * {@see DotModule}) render every module identically and ignore the parameter.
 */
interface ModuleShape
{
    public function svgPath(int $x, int $y, ModuleNeighbours $neighbours): string;

    /**
     * Hint for the SVG `shape-rendering` attribute. "crispEdges" for axis-
     * aligned rects (no anti-aliasing); "geometricPrecision" for curves.
     */
    public function shapeRendering(): string;
}
