<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Email;

it('produces a plain mailto: with no headers', function (): void {
    expect((string) new Email('a@b.com'))->toBe('mailto:a@b.com');
});

it('appends subject and body url-encoded', function (): void {
    $email = new Email('a@b.com', subject: 'hello world', body: 'line 1');
    expect((string) $email)->toBe('mailto:a@b.com?subject=hello%20world&body=line%201');
});

it('joins cc and bcc with commas', function (): void {
    $email = new Email('a@b.com', cc: ['c@b.com', 'd@b.com'], bcc: ['e@b.com']);
    expect((string) $email)->toBe('mailto:a@b.com?cc=c%40b.com%2Cd%40b.com&bcc=e%40b.com');
});

it('encodes special characters in headers', function (): void {
    $email = new Email('a@b.com', subject: 'Re: hi & bye');
    expect((string) $email)->toBe('mailto:a@b.com?subject=Re%3A%20hi%20%26%20bye');
});

it('throws on empty recipient', function (): void {
    expect(fn () => new Email(' '))->toThrow(PayloadException::class, 'to');
});
