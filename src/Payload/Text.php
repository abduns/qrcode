<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * Plain-text payload. Pass-through; included for API symmetry with the typed
 * payload helpers so callers can write QrCode::text(...) alongside
 * QrCode::url(...), QrCode::wifi(...), etc.
 */
final readonly class Text implements \Stringable
{
    public function __construct(public string $text)
    {
        if ($text === '') {
            throw PayloadException::emptyValue('text');
        }
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
