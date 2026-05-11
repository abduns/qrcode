<?php

declare(strict_types=1);

namespace Dunn\QrCode\Exception;

final class PayloadException extends QrCodeException
{
    public static function emptyValue(string $field): self
    {
        return new self(\sprintf('Payload field "%s" cannot be empty.', $field));
    }

    public static function latitudeOutOfRange(float $value): self
    {
        return new self(\sprintf('Latitude %F is out of range; must be between -90 and 90.', $value));
    }

    public static function longitudeOutOfRange(float $value): self
    {
        return new self(\sprintf('Longitude %F is out of range; must be between -180 and 180.', $value));
    }

    public static function invalidPhoneNumber(string $value): self
    {
        return new self(\sprintf('Invalid phone number %s; expected digits with optional leading "+".', \var_export($value, true)));
    }

    public static function eventEndsBeforeItStarts(): self
    {
        return new self('Event end time cannot be before the start time.');
    }
}
