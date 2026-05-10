<?php

declare(strict_types=1);

namespace Dunn\QrCode\Renderer\Png;

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Exception\InvalidConfigurationException;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Renderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\EyeStyle\CircleEyeInner;
use Dunn\QrCode\Style\EyeStyle\CircleEyeOuter;
use Dunn\QrCode\Style\EyeStyle\EyeInner;
use Dunn\QrCode\Style\EyeStyle\EyeOuter;
use Dunn\QrCode\Style\EyeStyle\SquareEyeInner;
use Dunn\QrCode\Style\EyeStyle\SquareEyeOuter;
use Dunn\QrCode\Style\Logo;
use Dunn\QrCode\Style\ModuleShape\DotModule;
use Dunn\QrCode\Style\ModuleShape\ModuleShape;
use Dunn\QrCode\Style\ModuleShape\SquareModule;
use GdImage;
use InvalidArgumentException;
use RuntimeException;

/**
 * Render a QR code to PNG bytes using the GD extension.
 *
 * Mirrors {@see \Dunn\QrCode\Renderer\Svg\SvgRenderer}'s feature set: three
 * independently-coloured regions (data dots, marker outer ring, marker inner
 * pupil) plus an optional center logo. Shape strategies are interpreted by
 * class identity — `SquareModule` becomes `imagefilledrectangle`, `DotModule`
 * becomes `imagefilledellipse`, etc. Custom shapes outside the bundled set
 * fall back to a filled rectangle (so adding new SVG shapes does not break
 * the PNG renderer; it just renders them as squares).
 *
 * Requires ext-gd (declared in composer.json `suggest`).
 */
final class GdPngRenderer implements Renderer
{
    private const MAX_LOGO_RATIO = [
        'L' => 0.26,
        'M' => 0.38,
        'Q' => 0.50,
        'H' => 0.54,
    ];

    private readonly Color $foreground;
    private readonly Color $background;
    private readonly ModuleShape $moduleShape;
    private readonly EyeOuter $eyeOuter;
    private readonly EyeInner $eyeInner;
    private readonly ?Color $dotColor;
    private readonly ?Color $markerOuterColor;
    private readonly ?Color $markerInnerColor;

    public function __construct(
        private readonly int $size = 300,
        private readonly int $margin = 4,
        Color|string $foreground = '#000000',
        Color|string $background = '#ffffff',
        ?ModuleShape $moduleShape = null,
        ?EyeOuter $eyeOuter = null,
        ?EyeInner $eyeInner = null,
        Color|string|null $dotColor = null,
        Color|string|null $markerOuterColor = null,
        Color|string|null $markerInnerColor = null,
        private readonly ?Logo $logo = null,
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

        $this->foreground = self::asColor($foreground);
        $this->background = self::asColor($background);
        $this->moduleShape = $moduleShape ?? new SquareModule();
        $this->eyeOuter = $eyeOuter ?? new SquareEyeOuter();
        $this->eyeInner = $eyeInner ?? new SquareEyeInner();
        $this->dotColor = $dotColor !== null ? self::asColor($dotColor) : null;
        $this->markerOuterColor = $markerOuterColor !== null ? self::asColor($markerOuterColor) : null;
        $this->markerInnerColor = $markerInnerColor !== null ? self::asColor($markerInnerColor) : null;
    }

    public function render(QrCode $qr): string
    {
        $this->validateLogoForEcc($qr->eccLevel);

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
            $bgColor = $this->allocate($img, $this->background);
            $dotPixel = $this->allocate($img, $this->dotColor ?? $this->foreground);
            $outerPixel = $this->allocate($img, $this->markerOuterColor ?? $this->foreground);
            $innerPixel = $this->allocate($img, $this->markerInnerColor ?? $this->foreground);

            imagefilledrectangle($img, 0, 0, $imageSize - 1, $imageSize - 1, $bgColor);

            // Data modules (skip finder areas — they're handled by the eye styles).
            for ($r = 0; $r < $modules; $r++) {
                for ($c = 0; $c < $modules; $c++) {
                    if (! $matrix->get($r, $c)) {
                        continue;
                    }
                    if (self::isInFinderArea($r, $c, $modules)) {
                        continue;
                    }
                    $this->drawModule(
                        $img,
                        ($c + $this->margin) * $moduleSize,
                        ($r + $this->margin) * $moduleSize,
                        $moduleSize,
                        $dotPixel,
                    );
                }
            }

            // Three eye regions: top-left, top-right, bottom-left.
            $corners = [
                [0, 0],
                [$modules - 7, 0],
                [0, $modules - 7],
            ];
            foreach ($corners as [$cellX, $cellY]) {
                $px = ($cellX + $this->margin) * $moduleSize;
                $py = ($cellY + $this->margin) * $moduleSize;
                $this->drawEyeOuter($img, $px, $py, $moduleSize, $outerPixel, $bgColor);
                $this->drawEyeInner($img, $px, $py, $moduleSize, $innerPixel);
            }

            // Optional logo overlay.
            $this->drawLogo($img, $modules, $moduleSize, $bgColor);

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

    private function drawModule(GdImage $img, int $x, int $y, int $scale, int $color): void
    {
        if ($this->moduleShape instanceof DotModule) {
            $cx = $x + intdiv($scale, 2);
            $cy = $y + intdiv($scale, 2);
            imagefilledellipse($img, $cx, $cy, $scale, $scale, $color);

            return;
        }
        // SquareModule (default) + any unknown shape: filled rectangle.
        imagefilledrectangle($img, $x, $y, $x + $scale - 1, $y + $scale - 1, $color);
    }

    private function drawEyeOuter(GdImage $img, int $x, int $y, int $scale, int $color, int $bg): void
    {
        $outer = 7 * $scale;

        if ($this->eyeOuter instanceof CircleEyeOuter) {
            $cx = $x + intdiv($outer, 2);
            $cy = $y + intdiv($outer, 2);
            // Outer dark disc (r = 3.5 modules), then carve a 2.5-module light hole.
            imagefilledellipse($img, $cx, $cy, $outer, $outer, $color);
            imagefilledellipse($img, $cx, $cy, 5 * $scale, 5 * $scale, $bg);

            return;
        }
        // SquareEyeOuter (default) + unknown: filled 7x7 outer, light 5x5 hole.
        imagefilledrectangle($img, $x, $y, $x + $outer - 1, $y + $outer - 1, $color);
        imagefilledrectangle(
            $img,
            $x + $scale,
            $y + $scale,
            $x + 6 * $scale - 1,
            $y + 6 * $scale - 1,
            $bg,
        );
    }

    private function drawEyeInner(GdImage $img, int $x, int $y, int $scale, int $color): void
    {
        $innerX = $x + 2 * $scale;
        $innerY = $y + 2 * $scale;
        $innerW = 3 * $scale;

        if ($this->eyeInner instanceof CircleEyeInner) {
            $cx = $innerX + intdiv($innerW, 2);
            $cy = $innerY + intdiv($innerW, 2);
            imagefilledellipse($img, $cx, $cy, $innerW, $innerW, $color);

            return;
        }
        // SquareEyeInner (default) + unknown: 3x3 filled rectangle.
        imagefilledrectangle($img, $innerX, $innerY, $innerX + $innerW - 1, $innerY + $innerW - 1, $color);
    }

    private function drawLogo(GdImage $img, int $modules, int $moduleSize, int $bg): void
    {
        if ($this->logo === null) {
            return;
        }

        $matrixPx = $modules * $moduleSize;
        $logoPx = max(1, (int) round($matrixPx * $this->logo->sizeRatio));
        $offset = $this->margin * $moduleSize + intdiv($matrixPx - $logoPx, 2);

        if ($this->logo->clearBackground) {
            $pad = max(1, (int) round($logoPx * 0.04));
            imagefilledrectangle(
                $img,
                $offset - $pad,
                $offset - $pad,
                $offset + $logoPx + $pad - 1,
                $offset + $logoPx + $pad - 1,
                $bg,
            );
        }

        // Suppress GD's E_WARNING via a temporary error handler — PHPUnit's
        // failOnWarning policy converts even @-suppressed warnings into errors.
        set_error_handler(static fn (int $n, string $m): bool => true);
        try {
            $logoImg = imagecreatefromstring($this->logo->bytes);
        } finally {
            restore_error_handler();
        }
        if ($logoImg === false) {
            throw new InvalidConfigurationException(
                'Failed to decode logo bytes; ext-gd only supports PNG/JPEG/GIF/WBMP, not SVG. '.
                'Use the SVG renderer for SVG logos, or pre-rasterise the logo.',
            );
        }

        imagealphablending($img, true);
        imagesavealpha($img, true);
        imagecopyresampled(
            $img,
            $logoImg,
            $offset,
            $offset,
            0,
            0,
            $logoPx,
            $logoPx,
            imagesx($logoImg),
            imagesy($logoImg),
        );
        imagedestroy($logoImg);
    }

    private function validateLogoForEcc(EccLevel $ecc): void
    {
        if ($this->logo === null) {
            return;
        }
        $max = self::MAX_LOGO_RATIO[$ecc->value];
        if ($this->logo->sizeRatio > $max) {
            throw new InvalidConfigurationException(sprintf(
                'Logo sizeRatio %.2f exceeds the safe maximum %.2f for ECC %s. '.
                'Increase the ECC level (e.g. Quartile/High) or shrink the logo.',
                $this->logo->sizeRatio,
                $max,
                $ecc->value,
            ));
        }
    }

    private function allocate(GdImage $img, Color $color): int
    {
        // Re-clamp so PHPStan sees int<0, 255> at the call site (the Color
        // constructor already validates, but the property is plain int).
        $pixel = imagecolorallocate(
            $img,
            max(0, min(255, $color->r)),
            max(0, min(255, $color->g)),
            max(0, min(255, $color->b)),
        );
        if ($pixel === false) {
            throw new RuntimeException('imagecolorallocate failed.');
        }

        return $pixel;
    }

    private static function asColor(Color|string $value): Color
    {
        return $value instanceof Color ? $value : Color::hex($value);
    }

    private static function isInFinderArea(int $r, int $c, int $size): bool
    {
        return ($r < 7 && $c < 7)
            || ($r < 7 && $c >= $size - 7)
            || ($r >= $size - 7 && $c < 7);
    }
}
