<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * geo: URI per RFC 5870. An optional human-readable label is appended as
 * ?q=<label>, which Google and Apple Maps both honour as a pin caption.
 */
final readonly class Geo implements \Stringable
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?string $label = null,
    ) {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw PayloadException::latitudeOutOfRange($latitude);
        }
        if ($longitude < -180.0 || $longitude > 180.0) {
            throw PayloadException::longitudeOutOfRange($longitude);
        }
    }

    public function __toString(): string
    {
        $base = 'geo:' . self::formatCoord($this->latitude) . ',' . self::formatCoord($this->longitude);
        if ($this->label !== null && $this->label !== '') {
            return $base . '?q=' . \rawurlencode($this->label);
        }

        return $base;
    }

    private static function formatCoord(float $value): string
    {
        // Up to 6 fractional digits (~0.11 m precision), trim trailing zeros.
        $formatted = \rtrim(\rtrim(\sprintf('%.6F', $value), '0'), '.');

        return $formatted === '' || $formatted === '-' ? '0' : $formatted;
    }
}
