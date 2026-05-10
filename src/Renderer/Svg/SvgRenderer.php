<?php

declare(strict_types=1);

namespace Dunn\QrCode\Renderer\Svg;

use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Renderer;
use Dunn\QrCode\Style\EyeStyle\EyeStyle;
use Dunn\QrCode\Style\EyeStyle\SquareEye;
use Dunn\QrCode\Style\ModuleShape\ModuleShape;
use Dunn\QrCode\Style\ModuleShape\SquareModule;
use InvalidArgumentException;

/**
 * Render a QR code to an `<svg>` element. Zero dependencies.
 *
 * Data modules are drawn via the supplied {@see ModuleShape} (default
 * {@see SquareModule}); the three 7×7 finder regions are drawn via the
 * supplied {@see EyeStyle} (default {@see SquareEye}). The renderer skips
 * the finder regions when iterating data modules so eye styles can fully
 * own that visual.
 *
 * The viewBox sizes to (modules + 2*margin); consumers scale via the
 * `width`/`height` attributes.
 */
final class SvgRenderer implements Renderer
{
    private readonly ModuleShape $moduleShape;
    private readonly EyeStyle $eyeStyle;

    public function __construct(
        private readonly int $size = 300,
        private readonly int $margin = 4,
        private readonly string $foreground = '#000000',
        private readonly string $background = '#ffffff',
        ?ModuleShape $moduleShape = null,
        ?EyeStyle $eyeStyle = null,
    ) {
        if ($size <= 0) {
            throw new InvalidArgumentException("size must be > 0, got {$size}");
        }
        if ($margin < 0) {
            throw new InvalidArgumentException("margin must be >= 0, got {$margin}");
        }

        $this->moduleShape = $moduleShape ?? new SquareModule();
        $this->eyeStyle = $eyeStyle ?? new SquareEye();
    }

    public function render(QrCode $qr): string
    {
        $matrix = $qr->matrix;
        $modules = $matrix->size();
        $total = $modules + 2 * $this->margin;

        $path = $this->buildPath($qr);

        // Choose shape-rendering hint: prefer the module shape's hint, but
        // upgrade to "geometricPrecision" if the eye style needs it (curves).
        $rendering = $this->moduleShape->shapeRendering();
        if ($this->eyeStyle->shapeRendering() === 'geometricPrecision') {
            $rendering = 'geometricPrecision';
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d" shape-rendering="%s">'
            .'<rect width="100%%" height="100%%" fill="%s"/>'
            .'<path d="%s" fill="%s" fill-rule="evenodd"/>'
            .'</svg>',
            $total,
            $total,
            $this->size,
            $this->size,
            htmlspecialchars($rendering, ENT_QUOTES | ENT_XML1),
            htmlspecialchars($this->background, ENT_QUOTES | ENT_XML1),
            $path,
            htmlspecialchars($this->foreground, ENT_QUOTES | ENT_XML1),
        );
    }

    public function mimeType(): string
    {
        return 'image/svg+xml';
    }

    private function buildPath(QrCode $qr): string
    {
        $matrix = $qr->matrix;
        $size = $matrix->size();
        $parts = [];

        // Data modules (skipping the three 7×7 finder regions).
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (! $matrix->get($r, $c)) {
                    continue;
                }
                if ($this->isInFinderArea($r, $c, $size)) {
                    continue;
                }
                $parts[] = $this->moduleShape->svgPath($c + $this->margin, $r + $this->margin);
            }
        }

        // Eyes: top-left, top-right, bottom-left.
        $parts[] = $this->eyeStyle->svgPath($this->margin, $this->margin);
        $parts[] = $this->eyeStyle->svgPath($size - 7 + $this->margin, $this->margin);
        $parts[] = $this->eyeStyle->svgPath($this->margin, $size - 7 + $this->margin);

        return implode('', $parts);
    }

    private function isInFinderArea(int $r, int $c, int $size): bool
    {
        return ($r < 7 && $c < 7)
            || ($r < 7 && $c >= $size - 7)
            || ($r >= $size - 7 && $c < 7);
    }
}
