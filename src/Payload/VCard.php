<?php

declare(strict_types=1);

namespace Dunn\QrCode\Payload;

use Dunn\QrCode\Exception\PayloadException;

/**
 * vCard 3.0 (RFC 2426) — the format with the broadest scanner support for
 * "scan to add contact" QR codes. Field values are escaped per RFC 6350 §3.4
 * (\ ; , and newlines get a leading backslash).
 *
 * Use the fluent with* / add* methods to compose; each returns a new
 * instance, mirroring the rest of the package's immutable builder style.
 */
final readonly class VCard implements \Stringable
{
    public const TYPE_WORK = 'WORK';
    public const TYPE_HOME = 'HOME';
    public const TYPE_CELL = 'CELL';

    /**
     * @param list<array{value: string, type: ?string}> $phones
     * @param list<array{value: string, type: ?string}> $emails
     */
    public function __construct(
        public string $fullName,
        public ?string $org = null,
        public ?string $title = null,
        public array $phones = [],
        public array $emails = [],
        public ?string $url = null,
        public ?string $address = null,
        public ?string $note = null,
    ) {
        if (\trim($fullName) === '') {
            throw PayloadException::emptyValue('fullName');
        }
    }

    public static function make(string $fullName): self
    {
        return new self($fullName);
    }

    public function withOrg(string $org): self
    {
        return new self($this->fullName, $org, $this->title, $this->phones, $this->emails, $this->url, $this->address, $this->note);
    }

    public function withTitle(string $title): self
    {
        return new self($this->fullName, $this->org, $title, $this->phones, $this->emails, $this->url, $this->address, $this->note);
    }

    public function addPhone(string $number, ?string $type = null): self
    {
        $phones = $this->phones;
        $phones[] = ['value' => $number, 'type' => $type];

        return new self($this->fullName, $this->org, $this->title, $phones, $this->emails, $this->url, $this->address, $this->note);
    }

    public function addEmail(string $email, ?string $type = null): self
    {
        $emails = $this->emails;
        $emails[] = ['value' => $email, 'type' => $type];

        return new self($this->fullName, $this->org, $this->title, $this->phones, $emails, $this->url, $this->address, $this->note);
    }

    public function withUrl(string $url): self
    {
        return new self($this->fullName, $this->org, $this->title, $this->phones, $this->emails, $url, $this->address, $this->note);
    }

    public function withAddress(string $address): self
    {
        return new self($this->fullName, $this->org, $this->title, $this->phones, $this->emails, $this->url, $address, $this->note);
    }

    public function withNote(string $note): self
    {
        return new self($this->fullName, $this->org, $this->title, $this->phones, $this->emails, $this->url, $this->address, $note);
    }

    public function __toString(): string
    {
        $lines = ['BEGIN:VCARD', 'VERSION:3.0'];

        $name = self::escape($this->fullName);
        $lines[] = 'FN:' . $name;
        $lines[] = 'N:' . $name . ';;;;';

        if ($this->org !== null && $this->org !== '') {
            $lines[] = 'ORG:' . self::escape($this->org);
        }
        if ($this->title !== null && $this->title !== '') {
            $lines[] = 'TITLE:' . self::escape($this->title);
        }

        foreach ($this->phones as $phone) {
            $tag = $phone['type'] !== null && $phone['type'] !== ''
                ? 'TEL;TYPE=' . $phone['type']
                : 'TEL';
            $lines[] = $tag . ':' . self::escape($phone['value']);
        }

        foreach ($this->emails as $email) {
            $tag = $email['type'] !== null && $email['type'] !== ''
                ? 'EMAIL;TYPE=' . $email['type']
                : 'EMAIL';
            $lines[] = $tag . ':' . self::escape($email['value']);
        }

        if ($this->url !== null && $this->url !== '') {
            $lines[] = 'URL:' . self::escape($this->url);
        }
        if ($this->address !== null && $this->address !== '') {
            $lines[] = 'ADR:;;' . self::escape($this->address) . ';;;;';
        }
        if ($this->note !== null && $this->note !== '') {
            $lines[] = 'NOTE:' . self::escape($this->note);
        }

        $lines[] = 'END:VCARD';

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
