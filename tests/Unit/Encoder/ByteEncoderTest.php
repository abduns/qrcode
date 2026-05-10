<?php

declare(strict_types=1);

use Dunn\QrCode\Encoder\BitBuffer;
use Dunn\QrCode\Encoder\ByteEncoder;

it('encodes each byte as 8 bits MSB-first', function (): void {
    $buf = new BitBuffer();
    (new ByteEncoder())->encode('Hi', $buf);

    expect($buf->size())->toBe(16);
    expect($buf->toBytes())->toBe([0x48, 0x69]);
});

it('treats empty input as a no-op', function (): void {
    $buf = new BitBuffer();
    (new ByteEncoder())->encode('', $buf);
    expect($buf->size())->toBe(0);
});

it('passes UTF-8 bytes through verbatim', function (): void {
    $buf = new BitBuffer();
    (new ByteEncoder())->encode("\xE2\x9C\x93", $buf); // ✓
    expect($buf->toBytes())->toBe([0xE2, 0x9C, 0x93]);
});
