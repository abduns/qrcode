<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * mailto: URI per RFC 6068. The address is taken verbatim; optional headers
 * (subject, body, cc, bcc) are appended as a URL-encoded query string.
 */
final readonly class Email implements \Stringable
{
    /**
     * @param list<string> $cc
     * @param list<string> $bcc
     */
    public function __construct(
        public string $to,
        public ?string $subject = null,
        public ?string $body = null,
        public array $cc = [],
        public array $bcc = [],
    ) {
        if (\trim($to) === '') {
            throw PayloadException::emptyValue('to');
        }
    }

    public function __toString(): string
    {
        $params = [];
        if ($this->cc !== []) {
            $params['cc'] = \implode(',', $this->cc);
        }
        if ($this->bcc !== []) {
            $params['bcc'] = \implode(',', $this->bcc);
        }
        if ($this->subject !== null && $this->subject !== '') {
            $params['subject'] = $this->subject;
        }
        if ($this->body !== null && $this->body !== '') {
            $params['body'] = $this->body;
        }

        $base = 'mailto:' . $this->to;
        if ($params === []) {
            return $base;
        }

        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = $key . '=' . \rawurlencode($value);
        }

        return $base . '?' . \implode('&', $pairs);
    }
}
