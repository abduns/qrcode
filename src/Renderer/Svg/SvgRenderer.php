<?php

declare(strict_types=1);

namespace Dunn\QrCode\Renderer\Svg;

use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Renderer;
use InvalidArgumentException;

/**
 * Render a QR code to an `<svg>` element. Zero dependencies.
 *
 * The output uses a single `<path>` consolidating consecutive dark modules
 * within each row, keeping the byte count small (typically <5 KB for a
 * URL-sized payload). The viewBox sizes to (modules + 2*margin) so the
 * consumer can scale via the `width`/`height` attributes.
 */
final class SvgRenderer implements Renderer
{
    public function __construct(
        private readonly int $size = 300,
        private readonly int $margin = 4,
        private readonly string $foreground = '#000000',
        private readonly string $background = '#ffffff',
    ) {
        if ($size <= 0) {
            throw new InvalidArgumentException("size must be > 0, got {$size}");
        }
        if ($margin < 0) {
            throw new InvalidArgumentException("margin must be >= 0, got {$margin}");
        }
    }

    public function render(QrCode $qr): string
    {
        $matrix = $qr->matrix;
        $modules = $matrix->size();
        $total = $modules + 2 * $this->margin;

        $path = $this->buildPath($qr, $this->margin);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d" shape-rendering="crispEdges">'
            .'<rect width="100%%" height="100%%" fill="%s"/>'
            .'<path d="%s" fill="%s"/>'
            .'</svg>',
            $total,
            $total,
            $this->size,
            $this->size,
            htmlspecialchars($this->background, ENT_QUOTES | ENT_XML1),
            $path,
            htmlspecialchars($this->foreground, ENT_QUOTES | ENT_XML1),
        );
    }

    public function mimeType(): string
    {
        return 'image/svg+xml';
    }

    /**
     * Per-row run consolidation: `M x y h W v 1 h -W z` per dark run.
     */
    private function buildPath(QrCode $qr, int $margin): string
    {
        $matrix = $qr->matrix;
        $size = $matrix->size();
        $parts = [];

        for ($r = 0; $r < $size; $r++) {
            $runStart = -1;
            for ($c = 0; $c <= $size; $c++) {
                $isDark = $c < $size && $matrix->get($r, $c);
                if ($isDark && $runStart === -1) {
                    $runStart = $c;
                } elseif (! $isDark && $runStart !== -1) {
                    $x = $runStart + $margin;
                    $y = $r + $margin;
                    $w = $c - $runStart;
                    $parts[] = "M{$x} {$y}h{$w}v1h-{$w}z";
                    $runStart = -1;
                }
            }
        }

        return implode('', $parts);
    }
}
