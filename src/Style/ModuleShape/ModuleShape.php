<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\ModuleShape;

/**
 * Strategy for rendering a single dark data module as an SVG path fragment.
 *
 * Implementations are passed (x, y) — the top-left of a 1×1 module in the
 * QR matrix's coordinate space (already shifted by the renderer's margin).
 * They return the SVG `d` attribute fragment that draws this module.
 */
interface ModuleShape
{
    public function svgPath(int $x, int $y): string;

    /**
     * Hint for the SVG `shape-rendering` attribute. "crispEdges" for axis-
     * aligned rects (no anti-aliasing); "auto" or "geometricPrecision" for
     * curves (so circles aren't pixelated).
     */
    public function shapeRendering(): string;
}
