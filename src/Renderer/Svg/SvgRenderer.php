<?php

declare(strict_types=1);

namespace Dunn\QrCode\Renderer\Svg;

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Exception\InvalidConfigurationException;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Renderer;
use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\EyeStyle\EyeInner;
use Dunn\QrCode\Style\EyeStyle\EyeOuter;
use Dunn\QrCode\Style\EyeStyle\SquareEyeInner;
use Dunn\QrCode\Style\EyeStyle\SquareEyeOuter;
use Dunn\QrCode\Style\Gradient\Gradient;
use Dunn\QrCode\Style\Logo;
use Dunn\QrCode\Style\ModuleShape\ModuleShape;
use Dunn\QrCode\Style\ModuleShape\SquareModule;
use InvalidArgumentException;

/**
 * Render a QR code to an `<svg>` element. Zero dependencies.
 *
 * The output is three independently-styled paths (data dots, marker outer
 * ring, marker inner pupil) plus an optional logo `<image>` embed. Each
 * region can have its own colour (Color/hex string) **or** Gradient.
 *
 * When no per-region paint is supplied, the {@see $foreground} paint is used.
 *
 * Logo overlays are validated against the QR's ECC level at render time —
 * a logo too large for the chosen ECC throws {@see InvalidConfigurationException}.
 */
final class SvgRenderer implements Renderer
{
    private const MAX_LOGO_RATIO = [
        'L' => 0.26,
        'M' => 0.38,
        'Q' => 0.50,
        'H' => 0.54,
    ];

    private readonly Color|Gradient $foreground;
    private readonly Color|Gradient $background;
    private readonly ModuleShape $moduleShape;
    private readonly EyeOuter $eyeOuter;
    private readonly EyeInner $eyeInner;
    private readonly Color|Gradient|null $dotColor;
    private readonly Color|Gradient|null $markerOuterColor;
    private readonly Color|Gradient|null $markerInnerColor;

    public function __construct(
        private readonly int $size = 300,
        private readonly int $margin = 4,
        Color|Gradient|string $foreground = '#000000',
        Color|Gradient|string $background = '#ffffff',
        ?ModuleShape $moduleShape = null,
        ?EyeOuter $eyeOuter = null,
        ?EyeInner $eyeInner = null,
        Color|Gradient|string|null $dotColor = null,
        Color|Gradient|string|null $markerOuterColor = null,
        Color|Gradient|string|null $markerInnerColor = null,
        private readonly ?Logo $logo = null,
    ) {
        if ($size <= 0) {
            throw new InvalidArgumentException("size must be > 0, got {$size}");
        }
        if ($margin < 0) {
            throw new InvalidArgumentException("margin must be >= 0, got {$margin}");
        }

        $this->foreground = self::asPaint($foreground);
        $this->background = self::asPaint($background);
        $this->moduleShape = $moduleShape ?? new SquareModule();
        $this->eyeOuter = $eyeOuter ?? new SquareEyeOuter();
        $this->eyeInner = $eyeInner ?? new SquareEyeInner();
        $this->dotColor = $dotColor !== null ? self::asPaint($dotColor) : null;
        $this->markerOuterColor = $markerOuterColor !== null ? self::asPaint($markerOuterColor) : null;
        $this->markerInnerColor = $markerInnerColor !== null ? self::asPaint($markerInnerColor) : null;
    }

    public function render(QrCode $qr): string
    {
        $this->validateLogoForEcc($qr->eccLevel);

        $matrix = $qr->matrix;
        $modules = $matrix->size();
        $total = $modules + 2 * $this->margin;

        $dotPath = $this->buildDotPath($qr);
        $outerPath = $this->buildOuterPath($qr);
        $innerPath = $this->buildInnerPath($qr);

        // Unique prefix so multiple SVGs on the same page don't collide on
        // gradient ids.
        $idPrefix = 'qr-'.bin2hex(random_bytes(3));

        $bg = $this->resolvePaint($this->background, $this->background, $idPrefix.'-bg');
        $dot = $this->resolvePaint($this->dotColor, $this->foreground, $idPrefix.'-dot');
        $outer = $this->resolvePaint($this->markerOuterColor, $this->foreground, $idPrefix.'-outer');
        $inner = $this->resolvePaint($this->markerInnerColor, $this->foreground, $idPrefix.'-inner');

        $defs = $bg['defs'].$dot['defs'].$outer['defs'].$inner['defs'];
        $defsBlock = $defs !== '' ? "<defs>{$defs}</defs>" : '';

        $rendering = $this->chooseShapeRendering();
        $logoEl = $this->buildLogoElement($modules, $bg['fill']);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d" shape-rendering="%s">'
            .'%s'
            .'<rect width="100%%" height="100%%" fill="%s"/>'
            .'<path d="%s" fill="%s" fill-rule="evenodd"/>'
            .'<path d="%s" fill="%s" fill-rule="evenodd"/>'
            .'<path d="%s" fill="%s" fill-rule="evenodd"/>'
            .'%s'
            .'</svg>',
            $total,
            $total,
            $this->size,
            $this->size,
            htmlspecialchars($rendering, ENT_QUOTES | ENT_XML1),
            $defsBlock,
            htmlspecialchars($bg['fill'], ENT_QUOTES | ENT_XML1),
            $dotPath,
            htmlspecialchars($dot['fill'], ENT_QUOTES | ENT_XML1),
            $outerPath,
            htmlspecialchars($outer['fill'], ENT_QUOTES | ENT_XML1),
            $innerPath,
            htmlspecialchars($inner['fill'], ENT_QUOTES | ENT_XML1),
            $logoEl,
        );
    }

    public function mimeType(): string
    {
        return 'image/svg+xml';
    }

    private function buildDotPath(QrCode $qr): string
    {
        $matrix = $qr->matrix;
        $size = $matrix->size();
        $parts = [];

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (! $matrix->get($r, $c)) {
                    continue;
                }
                if (self::isInFinderArea($r, $c, $size)) {
                    continue;
                }
                $parts[] = $this->moduleShape->svgPath($c + $this->margin, $r + $this->margin);
            }
        }

        return implode('', $parts);
    }

    private function buildOuterPath(QrCode $qr): string
    {
        $size = $qr->matrix->size();
        $corners = [
            [$this->margin, $this->margin],
            [$size - 7 + $this->margin, $this->margin],
            [$this->margin, $size - 7 + $this->margin],
        ];

        return implode('', array_map(
            fn (array $xy): string => $this->eyeOuter->svgPath($xy[0], $xy[1]),
            $corners,
        ));
    }

    private function buildInnerPath(QrCode $qr): string
    {
        $size = $qr->matrix->size();
        $corners = [
            [$this->margin, $this->margin],
            [$size - 7 + $this->margin, $this->margin],
            [$this->margin, $size - 7 + $this->margin],
        ];

        return implode('', array_map(
            fn (array $xy): string => $this->eyeInner->svgPath($xy[0], $xy[1]),
            $corners,
        ));
    }

    private function chooseShapeRendering(): string
    {
        foreach ([$this->moduleShape, $this->eyeOuter, $this->eyeInner] as $element) {
            if ($element->shapeRendering() === 'geometricPrecision') {
                return 'geometricPrecision';
            }
        }

        return 'crispEdges';
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

    private function buildLogoElement(int $modules, string $bgFill): string
    {
        if ($this->logo === null) {
            return '';
        }

        $logoSize = $modules * $this->logo->sizeRatio;
        $offset = $this->margin + ($modules - $logoSize) / 2;

        $parts = [];
        if ($this->logo->clearBackground) {
            $pad = max(0.5, $logoSize * 0.04);
            $parts[] = sprintf(
                '<rect x="%s" y="%s" width="%s" height="%s" fill="%s"/>',
                self::fmt($offset - $pad),
                self::fmt($offset - $pad),
                self::fmt($logoSize + 2 * $pad),
                self::fmt($logoSize + 2 * $pad),
                htmlspecialchars($bgFill, ENT_QUOTES | ENT_XML1),
            );
        }
        $parts[] = sprintf(
            '<image x="%s" y="%s" width="%s" height="%s" href="%s" preserveAspectRatio="xMidYMid meet"/>',
            self::fmt($offset),
            self::fmt($offset),
            self::fmt($logoSize),
            self::fmt($logoSize),
            htmlspecialchars($this->logo->dataUri(), ENT_QUOTES | ENT_XML1),
        );

        return implode('', $parts);
    }

    /**
     * Resolve a paint to a (fill, defs) pair. Gradients produce a `<defs>`
     * fragment and a `url(#…)` reference; Colors produce a hex/css fill and
     * empty defs.
     *
     * @return array{fill: string, defs: string}
     */
    private function resolvePaint(
        Color|Gradient|null $paint,
        Color|Gradient $fallback,
        string $id,
    ): array {
        $resolved = $paint ?? $fallback;

        if ($resolved instanceof Color) {
            return ['fill' => $resolved->toCss(), 'defs' => ''];
        }

        return [
            'fill' => 'url(#'.$id.')',
            'defs' => $resolved->defsFragment($id),
        ];
    }

    private static function asPaint(Color|Gradient|string $value): Color|Gradient
    {
        if ($value instanceof Color || $value instanceof Gradient) {
            return $value;
        }

        return Color::hex($value);
    }

    private static function isInFinderArea(int $r, int $c, int $size): bool
    {
        return ($r < 7 && $c < 7)
            || ($r < 7 && $c >= $size - 7)
            || ($r >= $size - 7 && $c < 7);
    }

    private static function fmt(float $v): string
    {
        if ($v === (float) (int) $v) {
            return (string) (int) $v;
        }
        $s = sprintf('%.3f', $v);

        return rtrim(rtrim($s, '0'), '.');
    }
}
