<?php

declare(strict_types=1);

namespace Dunn\QrCode\Renderer\Png;

use Dunn\QrCode\Exception\InvalidConfigurationException;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Renderer;
use InvalidArgumentException;
use RuntimeException;

/**
 * Render a QR code to PNG bytes using the GD extension.
 *
 * The output module size is computed as floor(size / (modules + 2*margin)).
 * The actual image is sized to (modules + 2*margin) * moduleSize so each
 * module is an integer pixel width — keeps edges crisp without anti-aliasing.
 *
 * Requires ext-gd (declared in composer.json `suggest`).
 */
final class GdPngRenderer implements Renderer
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
        if (! extension_loaded('gd')) {
            throw new InvalidConfigurationException('GdPngRenderer requires ext-gd.');
        }
    }

    public function render(QrCode $qr): string
    {
        $matrix = $qr->matrix;
        $modules = $matrix->size();
        $total = $modules + 2 * $this->margin;

        $moduleSize = max(1, intdiv($this->size, $total));
        $imageSize = max(1, $total * $moduleSize);

        $img = imagecreatetruecolor($imageSize, $imageSize);
        if ($img === false) {
            throw new RuntimeException('imagecreatetruecolor failed.');
        }

        try {
            $bg = $this->allocate($img, $this->background);
            $fg = $this->allocate($img, $this->foreground);

            imagefilledrectangle($img, 0, 0, $imageSize - 1, $imageSize - 1, $bg);

            for ($r = 0; $r < $modules; $r++) {
                for ($c = 0; $c < $modules; $c++) {
                    if (! $matrix->get($r, $c)) {
                        continue;
                    }
                    $x = ($c + $this->margin) * $moduleSize;
                    $y = ($r + $this->margin) * $moduleSize;
                    imagefilledrectangle(
                        $img,
                        $x,
                        $y,
                        $x + $moduleSize - 1,
                        $y + $moduleSize - 1,
                        $fg,
                    );
                }
            }

            ob_start();
            imagepng($img);
            $png = ob_get_clean();
        } finally {
            imagedestroy($img);
        }

        if ($png === false || $png === '') {
            throw new RuntimeException('imagepng produced empty output.');
        }

        return $png;
    }

    public function mimeType(): string
    {
        return 'image/png';
    }

    private function allocate(\GdImage $img, string $hex): int
    {
        [$r, $g, $b] = $this->parseHexColor($hex);
        $color = imagecolorallocate($img, $r, $g, $b);
        if ($color === false) {
            throw new RuntimeException("imagecolorallocate failed for {$hex}.");
        }

        return $color;
    }

    /**
     * @return array{0: int<0, 255>, 1: int<0, 255>, 2: int<0, 255>}
     */
    private function parseHexColor(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6 || preg_match('/^[0-9a-fA-F]{6}$/', $hex) !== 1) {
            throw new InvalidArgumentException("Invalid hex color: {$hex}");
        }

        // hexdec on a 2-char hex string is always in 0..255; clamp for PHPStan.
        return [
            max(0, min(255, (int) hexdec(substr($hex, 0, 2)))),
            max(0, min(255, (int) hexdec(substr($hex, 2, 2)))),
            max(0, min(255, (int) hexdec(substr($hex, 4, 2)))),
        ];
    }
}
