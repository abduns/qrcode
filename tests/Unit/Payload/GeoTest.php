<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Geo;

it('produces a geo: URI with sane precision', function (): void {
    expect((string) new Geo(37.7749, -122.4194))->toBe('geo:37.7749,-122.4194');
});

it('appends ?q= when a label is provided', function (): void {
    expect((string) new Geo(37.7749, -122.4194, 'San Francisco'))
        ->toBe('geo:37.7749,-122.4194?q=San%20Francisco');
});

it('trims trailing zeros from whole numbers', function (): void {
    expect((string) new Geo(0.0, 0.0))->toBe('geo:0,0');
});

it('rejects latitudes outside [-90, 90]', function (): void {
    expect(fn () => new Geo(91.0, 0.0))->toThrow(PayloadException::class, 'Latitude');
    expect(fn () => new Geo(-90.1, 0.0))->toThrow(PayloadException::class, 'Latitude');
});

it('rejects longitudes outside [-180, 180]', function (): void {
    expect(fn () => new Geo(0.0, 181.0))->toThrow(PayloadException::class, 'Longitude');
});
