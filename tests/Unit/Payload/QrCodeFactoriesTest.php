<?php

declare(strict_types=1);

use Dunn\QrCode\Builder;
use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\Payload\Event;
use Dunn\QrCode\Payload\VCard;
use Dunn\QrCode\Payload\WifiAuth;
use Dunn\QrCode\QrCode;

it('QrCode::create() accepts a Stringable payload', function (): void {
    $payload = new class () implements \Stringable {
        public function __toString(): string
        {
            return 'HELLO';
        }
    };

    $qr = QrCode::create($payload)->build();
    expect($qr)->toBeInstanceOf(QrCode::class);
});

it('QrCode::url() returns a Builder that produces a Byte-mode QR', function (): void {
    $qr = QrCode::url('https://example.com')->build();
    expect($qr->mode)->toBe(Mode::Byte);
});

it('QrCode::text() returns a Builder', function (): void {
    expect(QrCode::text('hello'))->toBeInstanceOf(Builder::class);
});

it('QrCode::phone() builds an end-to-end QR', function (): void {
    expect(QrCode::phone('+14155550123')->build())->toBeInstanceOf(QrCode::class);
});

it('QrCode::sms() builds an end-to-end QR', function (): void {
    expect(QrCode::sms('+14155550123', 'hi')->build())->toBeInstanceOf(QrCode::class);
});

it('QrCode::email() builds an end-to-end QR', function (): void {
    expect(QrCode::email('a@b.com', subject: 'hi')->build())->toBeInstanceOf(QrCode::class);
});

it('QrCode::geo() builds an end-to-end QR', function (): void {
    expect(QrCode::geo(37.7749, -122.4194)->build())->toBeInstanceOf(QrCode::class);
});

it('QrCode::wifi() builds an end-to-end QR', function (): void {
    expect(QrCode::wifi('MyNet', 'secret', WifiAuth::WPA)->build())->toBeInstanceOf(QrCode::class);
});

it('QrCode::vCard() builds an end-to-end QR', function (): void {
    $card = VCard::make('John Doe')->withOrg('Acme');
    expect(QrCode::vCard($card)->build())->toBeInstanceOf(QrCode::class);
});

it('QrCode::event() builds an end-to-end QR', function (): void {
    $event = Event::make('Launch')
        ->from(new \DateTimeImmutable('2026-06-01 18:00', new \DateTimeZone('UTC')))
        ->to(new \DateTimeImmutable('2026-06-01 22:00', new \DateTimeZone('UTC')));

    expect(QrCode::event($event)->build())->toBeInstanceOf(QrCode::class);
});
