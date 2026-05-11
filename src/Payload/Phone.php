<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * tel: URI per RFC 3966. Spaces, dashes and parentheses are stripped; the
 * result must be all digits with an optional leading "+".
 */
final readonly class Phone implements \Stringable
{
    public string $number;

    public function __construct(string $number)
    {
        $normalized = \preg_replace('/[\s()\-]/', '', $number) ?? '';
        if ($normalized === '' || \preg_match('/^\+?\d+$/', $normalized) !== 1) {
            throw PayloadException::invalidPhoneNumber($number);
        }
        $this->number = $normalized;
    }

    public function __toString(): string
    {
        return 'tel:' . $this->number;
    }
}
