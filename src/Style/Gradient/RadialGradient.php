<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\Gradient;

use InvalidArgumentException;

/**
 * Radial gradient centered at (cx, cy) with radius $r, in
 * `objectBoundingBox` units (0..1 across the filled shape's bounding box).
 * The default centers the gradient at the shape's middle.
 *
 *   new RadialGradient([
 *       new GradientStop(0.0, Color::hex('#e76f51')),
 *       new GradientStop(1.0, Color::hex('#264653')),
 *   ])  // glowing center
 */
final readonly class RadialGradient implements Gradient
{
    /**
     * @param list<GradientStop> $stops
     */
    public function __construct(
        public array $stops,
        public float $cx = 0.5,
        public float $cy = 0.5,
        public float $r = 0.5,
    ) {
        if (count($stops) < 2) {
            throw new InvalidArgumentException('RadialGradient requires at least two GradientStop entries.');
        }
        if ($r <= 0.0) {
            throw new InvalidArgumentException("RadialGradient radius must be > 0; got {$r}");
        }
    }

    public function defsFragment(string $id): string
    {
        $stops = '';
        foreach ($this->stops as $stop) {
            $stops .= sprintf(
                '<stop offset="%s" stop-color="%s" stop-opacity="%s"/>',
                self::fmt($stop->offset),
                $stop->color->toHex(),
                self::fmt($stop->color->alpha),
            );
        }

        return sprintf(
            '<radialGradient id="%s" cx="%s" cy="%s" r="%s">%s</radialGradient>',
            htmlspecialchars($id, ENT_QUOTES | ENT_XML1),
            self::fmt($this->cx),
            self::fmt($this->cy),
            self::fmt($this->r),
            $stops,
        );
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
