<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Sms;

it('defaults to the SMSTO: form without a body', function (): void {
    expect((string) new Sms('+14155550123'))->toBe('SMSTO:+14155550123');
});

it('appends the body after a colon in SMSTO: form', function (): void {
    expect((string) new Sms('+14155550123', 'hi there'))
        ->toBe('SMSTO:+14155550123:hi there');
});

it('emits an sms: URI when useSmsUri is true', function (): void {
    expect((string) new Sms('+14155550123', 'hi there', useSmsUri: true))
        ->toBe('sms:+14155550123?body=hi%20there');
});

it('omits the query when sms: URI has no body', function (): void {
    expect((string) new Sms('+14155550123', useSmsUri: true))->toBe('sms:+14155550123');
});

it('throws on an invalid number', function (): void {
    expect(fn () => new Sms('not-a-number'))->toThrow(PayloadException::class);
});
