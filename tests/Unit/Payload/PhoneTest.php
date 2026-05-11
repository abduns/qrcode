<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Phone;

it('produces a tel: URI', function (): void {
    expect((string) new Phone('+14155550123'))->toBe('tel:+14155550123');
});

it('strips spaces, dashes and parentheses', function (): void {
    expect((string) new Phone('+1 (415) 555-0123'))->toBe('tel:+14155550123');
});

it('accepts numbers without a leading +', function (): void {
    expect((string) new Phone('4155550123'))->toBe('tel:4155550123');
});

it('throws on letters', function (): void {
    expect(fn () => new Phone('+1-415-CALL-ME'))->toThrow(PayloadException::class);
});

it('throws on empty input', function (): void {
    expect(fn () => new Phone('  '))->toThrow(PayloadException::class);
});
