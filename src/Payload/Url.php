<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * URL payload. Encoded verbatim — scanners detect the scheme themselves.
 */
final readonly class Url implements \Stringable
{
    public string $url;

    public function __construct(string $url)
    {
        $trimmed = \trim($url);
        if ($trimmed === '') {
            throw PayloadException::emptyValue('url');
        }
        $this->url = $trimmed;
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
