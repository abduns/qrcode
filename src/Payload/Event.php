<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * iCalendar 2.0 VEVENT (RFC 5545) — "scan to add to calendar". UID and
 * DTSTAMP are auto-generated; all timestamps are serialised in UTC as
 * YYYYMMDDTHHMMSSZ.
 */
final readonly class Event implements \Stringable
{
    public function __construct(
        public string $summary,
        public ?\DateTimeImmutable $start = null,
        public ?\DateTimeImmutable $end = null,
        public ?string $location = null,
        public ?string $description = null,
        public ?string $url = null,
        public ?string $uid = null,
    ) {
        if (\trim($summary) === '') {
            throw PayloadException::emptyValue('summary');
        }
        if ($start !== null && $end !== null && $end < $start) {
            throw PayloadException::eventEndsBeforeItStarts();
        }
    }

    public static function make(string $summary): self
    {
        return new self($summary);
    }

    public function from(\DateTimeImmutable $start): self
    {
        return new self($this->summary, $start, $this->end, $this->location, $this->description, $this->url, $this->uid);
    }

    public function to(\DateTimeImmutable $end): self
    {
        return new self($this->summary, $this->start, $end, $this->location, $this->description, $this->url, $this->uid);
    }

    public function at(string $location): self
    {
        return new self($this->summary, $this->start, $this->end, $location, $this->description, $this->url, $this->uid);
    }

    public function withDescription(string $description): self
    {
        return new self($this->summary, $this->start, $this->end, $this->location, $description, $this->url, $this->uid);
    }

    public function withUrl(string $url): self
    {
        return new self($this->summary, $this->start, $this->end, $this->location, $this->description, $url, $this->uid);
    }

    public function withUid(string $uid): self
    {
        return new self($this->summary, $this->start, $this->end, $this->location, $this->description, $this->url, $uid);
    }

    public function __toString(): string
    {
        $utc = new \DateTimeZone('UTC');
        $now = (new \DateTimeImmutable('now', $utc))->format('Ymd\THis\Z');
        $uid = $this->uid ?? \bin2hex(\random_bytes(8)) . '@abduns-qrcode';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//abduns/qrcode//EN',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $now,
            'SUMMARY:' . self::escape($this->summary),
        ];

        if ($this->start !== null) {
            $lines[] = 'DTSTART:' . $this->start->setTimezone($utc)->format('Ymd\THis\Z');
        }
        if ($this->end !== null) {
            $lines[] = 'DTEND:' . $this->end->setTimezone($utc)->format('Ymd\THis\Z');
        }
        if ($this->location !== null && $this->location !== '') {
            $lines[] = 'LOCATION:' . self::escape($this->location);
        }
        if ($this->description !== null && $this->description !== '') {
            $lines[] = 'DESCRIPTION:' . self::escape($this->description);
        }
        if ($this->url !== null && $this->url !== '') {
            $lines[] = 'URL:' . self::escape($this->url);
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return \implode("\r\n", $lines);
    }

    private static function escape(string $value): string
    {
        return \strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ',' => '\\,',
            "\r\n" => '\\n',
            "\n" => '\\n',
            "\r" => '\\n',
        ]);
    }
}
