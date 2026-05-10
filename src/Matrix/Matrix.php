<?php

declare(strict_types=1);

namespace Dunn\QrCode\Matrix;

use InvalidArgumentException;

/**
 * Mutable square QR matrix: `4 * version + 17` modules per side.
 *
 * Each cell carries a colour (false = light, true = dark) and a "reserved"
 * flag — function patterns and format/version info regions are reserved so
 * the data placer skips them and the masker leaves them untouched.
 */
final class Matrix
{
    private readonly int $version;
    private readonly int $size;

    /** @var array<int, array<int, bool>> */
    private array $modules;

    /** @var array<int, array<int, bool>> */
    private array $reserved;

    public function __construct(int $version)
    {
        if ($version < 1 || $version > 40) {
            throw new InvalidArgumentException("Version must be in 1..40, got {$version}");
        }

        $this->version = $version;
        $this->size = 4 * $version + 17;
        $this->modules = array_fill(0, $this->size, array_fill(0, $this->size, false));
        $this->reserved = array_fill(0, $this->size, array_fill(0, $this->size, false));
    }

    public function version(): int
    {
        return $this->version;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function set(int $row, int $col, bool $value): void
    {
        $this->modules[$row][$col] = $value;
    }

    public function get(int $row, int $col): bool
    {
        return $this->modules[$row][$col];
    }

    public function reserve(int $row, int $col): void
    {
        $this->reserved[$row][$col] = true;
    }

    public function isReserved(int $row, int $col): bool
    {
        return $this->reserved[$row][$col];
    }

    /**
     * Set a value AND reserve the cell. Used by function patterns.
     */
    public function setFunction(int $row, int $col, bool $value): void
    {
        $this->modules[$row][$col] = $value;
        $this->reserved[$row][$col] = true;
    }

    public function toAscii(string $dark = '##', string $light = '  '): string
    {
        $lines = [];
        for ($r = 0; $r < $this->size; $r++) {
            $line = '';
            for ($c = 0; $c < $this->size; $c++) {
                $line .= $this->modules[$r][$c] ? $dark : $light;
            }
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
