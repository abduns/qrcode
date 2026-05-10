<?php

declare(strict_types=1);

use Dunn\QrCode\QrCode;

it('exposes a static create() entry point that holds the input', function (): void {
    $qr = QrCode::create('https://example.com');

    expect($qr)->toBeInstanceOf(QrCode::class);
    expect($qr->getData())->toBe('https://example.com');
});

it('treats two instances built from the same input as separate objects', function (): void {
    $a = QrCode::create('foo');
    $b = QrCode::create('foo');

    expect($a)->not->toBe($b);
    expect($a->getData())->toBe($b->getData());
});
