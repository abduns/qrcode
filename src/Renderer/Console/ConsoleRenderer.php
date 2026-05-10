<?php

declare(strict_types=1);

namespace Dunn\QrCode\Renderer\Console;

use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Renderer;

/**
 * Render a QR code as monospace text using Unicode block characters.
 * Primarily a debugging aid; useful for visualizing matrices in tests.
 *
 * Dark modules render as two filled blocks (██), light as two spaces, so
 * each module is roughly square on most terminals.
 */
final class ConsoleRenderer implements Renderer
{
    public function __construct(
        private readonly int $margin = 2,
        private readonly string $darkGlyph = '██',
        private readonly string $lightGlyph = '  ',
    ) {
    }

    public function render(QrCode $qr): string
    {
        $matrix = $qr->matrix;
        $size = $matrix->size();
        $totalWidth = $size + 2 * $this->margin;

        $lines = [];
        for ($i = 0; $i < $this->margin; $i++) {
            $lines[] = str_repeat($this->lightGlyph, $totalWidth);
        }

        for ($r = 0; $r < $size; $r++) {
            $line = str_repeat($this->lightGlyph, $this->margin);
            for ($c = 0; $c < $size; $c++) {
                $line .= $matrix->get($r, $c) ? $this->darkGlyph : $this->lightGlyph;
            }
            $line .= str_repeat($this->lightGlyph, $this->margin);
            $lines[] = $line;
        }

        for ($i = 0; $i < $this->margin; $i++) {
            $lines[] = str_repeat($this->lightGlyph, $totalWidth);
        }

        return implode("\n", $lines) . "\n";
    }

    public function mimeType(): string
    {
        return 'text/plain';
    }
}
