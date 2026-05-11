<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * SMS payload. Defaults to the SMSTO:<number>:<body> form which is the most
 * widely-recognised scanner convention; pass useSmsUri:true to emit a plain
 * sms:<number>?body=... URI instead.
 */
final readonly class Sms implements \Stringable
{
    public string $number;

    public function __construct(
        string $number,
        public ?string $body = null,
        public bool $useSmsUri = false,
    ) {
        $normalized = \preg_replace('/[\s()\-]/', '', $number) ?? '';
        if ($normalized === '' || \preg_match('/^\+?\d+$/', $normalized) !== 1) {
            throw PayloadException::invalidPhoneNumber($number);
        }
        $this->number = $normalized;
    }

    public function __toString(): string
    {
        if ($this->useSmsUri) {
            $base = 'sms:' . $this->number;

            return $this->body !== null && $this->body !== ''
                ? $base . '?body=' . \rawurlencode($this->body)
                : $base;
        }

        return $this->body !== null && $this->body !== ''
            ? 'SMSTO:' . $this->number . ':' . $this->body
            : 'SMSTO:' . $this->number;
    }
}
