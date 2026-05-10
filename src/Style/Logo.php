<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style;

use InvalidArgumentException;
use RuntimeException;

/**
 * Center overlay image for a QR code. Embedded by renderers as a data URI.
 *
 * The {@see $sizeRatio} is the fraction of the QR-module-grid side length
 * occupied by the logo (e.g. 0.2 → a 20% × 20% center patch = 4% of the
 * total area). Renderers validate this ratio against the QR's ECC level
 * at render time and throw if it would exceed the recoverable percentage.
 */
final readonly class Logo
{
    /** Supported logo MIME types. */
    public const SUPPORTED_MIME = [
        'image/svg+xml',
        'image/png',
        'image/jpeg',
        'image/gif',
    ];

    public function __construct(
        public string $bytes,
        public string $mimeType,
        public float $sizeRatio = 0.2,
        public bool $clearBackground = true,
    ) {
        if ($sizeRatio <= 0.0 || $sizeRatio > 0.55) {
            throw new InvalidArgumentException(
                "Logo sizeRatio must be in (0.0, 0.55]; got {$sizeRatio}",
            );
        }
        if (! in_array($mimeType, self::SUPPORTED_MIME, true)) {
            throw new InvalidArgumentException(
                "Unsupported logo MIME type '{$mimeType}'. Supported: ".implode(', ', self::SUPPORTED_MIME),
            );
        }
        if ($bytes === '') {
            throw new InvalidArgumentException('Logo bytes cannot be empty.');
        }
    }

    /**
     * Load a logo from disk, auto-detecting the MIME type from the extension.
     */
    public static function fromFile(string $path, float $sizeRatio = 0.2, bool $clearBackground = true): self
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("Logo file not found or unreadable: {$path}");
        }
        $bytes = @file_get_contents($path);
        if ($bytes === false) {
            throw new RuntimeException("Failed to read logo file: {$path}");
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            default => throw new InvalidArgumentException("Unsupported logo extension '.{$ext}'."),
        };

        return new self($bytes, $mime, $sizeRatio, $clearBackground);
    }

    /**
     * Format the bytes as an inlineable `data:` URI.
     */
    public function dataUri(): string
    {
        return 'data:'.$this->mimeType.';base64,'.base64_encode($this->bytes);
    }
}
