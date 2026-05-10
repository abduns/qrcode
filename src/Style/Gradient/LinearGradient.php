<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\Gradient;

use InvalidArgumentException;

/**
 * Linear gradient from (x1, y1) to (x2, y2) in `objectBoundingBox` units
 * (0..1 along each axis of the filled shape's bounding box).
 *
 *   new LinearGradient([
 *       new GradientStop(0.0, Color::hex('#264653')),
 *       new GradientStop(1.0, Color::hex('#2a9d8f')),
 *   ])  // default: top-left → bottom-right
 *
 * Supply (x1, y1, x2, y2) to control direction. For top-to-bottom use
 * (0, 0, 0, 1); left-to-right (0, 0, 1, 0); diagonals are the obvious mix.
 */
final readonly class LinearGradient implements Gradient
{
    /**
     * @param list<GradientStop> $stops
     */
    public function __construct(
        public array $stops,
        public float $x1 = 0.0,
        public float $y1 = 0.0,
        public float $x2 = 1.0,
        public float $y2 = 1.0,
    ) {
        if (count($stops) < 2) {
            throw new InvalidArgumentException('LinearGradient requires at least two GradientStop entries.');
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
            '<linearGradient id="%s" x1="%s" y1="%s" x2="%s" y2="%s">%s</linearGradient>',
            htmlspecialchars($id, ENT_QUOTES | ENT_XML1),
            self::fmt($this->x1),
            self::fmt($this->y1),
            self::fmt($this->x2),
            self::fmt($this->y2),
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
