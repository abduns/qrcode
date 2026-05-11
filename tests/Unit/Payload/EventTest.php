<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Event;

it('produces a VEVENT wrapped in VCALENDAR', function (): void {
    $out = (string) Event::make('Launch party');

    expect($out)->toStartWith('BEGIN:VCALENDAR');
    expect($out)->toEndWith('END:VCALENDAR');
    expect($out)->toContain('BEGIN:VEVENT');
    expect($out)->toContain('END:VEVENT');
    expect($out)->toContain('SUMMARY:Launch party');
    expect($out)->toContain('VERSION:2.0');
    expect($out)->toContain('PRODID:-//abduns/qrcode//EN');
});

it('serialises DTSTART and DTEND in UTC compact form', function (): void {
    $event = Event::make('Meeting')
        ->from(new \DateTimeImmutable('2026-06-01 18:00:00', new \DateTimeZone('UTC')))
        ->to(new \DateTimeImmutable('2026-06-01 22:30:00', new \DateTimeZone('UTC')));

    $out = (string) $event;
    expect($out)->toContain('DTSTART:20260601T180000Z');
    expect($out)->toContain('DTEND:20260601T223000Z');
});

it('normalises non-UTC timezones to UTC', function (): void {
    $event = Event::make('Meeting')
        ->from(new \DateTimeImmutable('2026-06-01 12:00:00', new \DateTimeZone('America/Los_Angeles')));

    expect((string) $event)->toContain('DTSTART:20260601T190000Z');
});

it('emits LOCATION, DESCRIPTION and URL when set', function (): void {
    $event = Event::make('Meeting')
        ->at('HQ')
        ->withDescription('See you there')
        ->withUrl('https://acme.com/launch');

    $out = (string) $event;
    expect($out)->toContain('LOCATION:HQ');
    expect($out)->toContain('DESCRIPTION:See you there');
    expect($out)->toContain('URL:https://acme.com/launch');
});

it('auto-generates UID and DTSTAMP when not provided', function (): void {
    $out = (string) Event::make('M');
    expect($out)->toMatch('/UID:[a-f0-9]{16}@abduns-qrcode/');
    expect($out)->toMatch('/DTSTAMP:\d{8}T\d{6}Z/');
});

it('respects an explicit UID', function (): void {
    $out = (string) Event::make('M')->withUid('fixed@example');
    expect($out)->toContain('UID:fixed@example');
});

it('escapes special characters in summary/description/location', function (): void {
    $event = Event::make('Re; party')
        ->withDescription("line 1\nline 2");

    $out = (string) $event;
    expect($out)->toContain('SUMMARY:Re\\; party');
    expect($out)->toContain('DESCRIPTION:line 1\\nline 2');
});

it('throws when end is before start', function (): void {
    $start = new \DateTimeImmutable('2026-06-01 18:00', new \DateTimeZone('UTC'));
    $end = new \DateTimeImmutable('2026-06-01 17:00', new \DateTimeZone('UTC'));

    expect(fn () => Event::make('M')->from($start)->to($end))
        ->toThrow(PayloadException::class, 'end');
});

it('throws on empty summary', function (): void {
    expect(fn () => Event::make(' '))->toThrow(PayloadException::class, 'summary');
});
