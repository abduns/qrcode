<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Url;

it('returns the URL verbatim', function (): void {
    expect((string) new Url('https://example.com/path?q=1'))
        ->toBe('https://example.com/path?q=1');
});

it('trims surrounding whitespace', function (): void {
    expect((string) new Url("  https://example.com\n"))
        ->toBe('https://example.com');
});

it('throws on empty input', function (): void {
    expect(fn () => new Url('   '))->toThrow(PayloadException::class, 'url');
});

it('is Stringable', function (): void {
    $url = new Url('https://example.com');
    expect($url)->toBeInstanceOf(\Stringable::class);
    expect((string) $url)->toBe($url->__toString());
});
