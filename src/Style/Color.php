<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style;

use InvalidArgumentException;

/**
 * Immutable RGBA colour. Channel values are 0..255; alpha is 0.0..1.0.
 *
 *     Color::hex('#1a1a2e')
 *     Color::rgb(26, 26, 46)
 *     Color::rgba(26, 26, 46, 0.85)
 */
final readonly class Color
{
    public function __construct(
        public int $r,
        public int $g,
        public int $b,
        public float $alpha = 1.0,
    ) {
        if ($r < 0 || $r > 255 || $g < 0 || $g > 255 || $b < 0 || $b > 255) {
            throw new InvalidArgumentException('Color channels must be in 0..255.');
        }
        if ($alpha < 0.0 || $alpha > 1.0) {
            throw new InvalidArgumentException('alpha must be in 0.0..1.0.');
        }
    }

    public static function hex(string $hex): self
    {
        $clean = ltrim($hex, '#');
        if (strlen($clean) === 3) {
            $clean = $clean[0].$clean[0].$clean[1].$clean[1].$clean[2].$clean[2];
        }
        if (strlen($clean) !== 6 || preg_match('/^[0-9a-fA-F]{6}$/', $clean) !== 1) {
            throw new InvalidArgumentException("Invalid hex color: {$hex}");
        }

        return new self(
            r: max(0, min(255, (int) hexdec(substr($clean, 0, 2)))),
            g: max(0, min(255, (int) hexdec(substr($clean, 2, 2)))),
            b: max(0, min(255, (int) hexdec(substr($clean, 4, 2)))),
        );
    }

    public static function rgb(int $r, int $g, int $b): self
    {
        return new self($r, $g, $b);
    }

    public static function rgba(int $r, int $g, int $b, float $alpha): self
    {
        return new self($r, $g, $b, $alpha);
    }

    public static function black(): self
    {
        return new self(0, 0, 0);
    }

    public static function white(): self
    {
        return new self(255, 255, 255);
    }

    public function toHex(): string
    {
        return sprintf('#%02x%02x%02x', $this->r, $this->g, $this->b);
    }

    public function toCss(): string
    {
        if ($this->alpha === 1.0) {
            return $this->toHex();
        }

        return sprintf('rgba(%d,%d,%d,%s)', $this->r, $this->g, $this->b, rtrim(rtrim((string) $this->alpha, '0'), '.'));
    }
}
