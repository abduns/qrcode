<?php

declare(strict_types=1);

use Dunn\QrCode\Encoder\BitBuffer;

it('starts empty', function (): void {
    $buf = new BitBuffer();
    expect($buf->size())->toBe(0);
    expect($buf->toBytes())->toBe([]);
});

it('writes bits MSB-first within an appended value', function (): void {
    $buf = new BitBuffer();
    $buf->appendBits(0b101, 3);
    expect($buf->getBits())->toBe([1, 0, 1]);
});

it('zero-pads a trailing partial byte', function (): void {
    $buf = new BitBuffer();
    $buf->appendBits(0b101, 3);
    expect($buf->toBytes())->toBe([0b10100000]);
});

it('preserves an exact byte boundary without padding', function (): void {
    $buf = new BitBuffer();
    $buf->appendBits(0xAB, 8);
    expect($buf->toBytes())->toBe([0xAB]);
});

it('rejects bitCount out of 0..31', function (): void {
    $buf = new BitBuffer();
    expect(fn () => $buf->appendBits(0, -1))->toThrow(InvalidArgumentException::class);
    expect(fn () => $buf->appendBits(0, 32))->toThrow(InvalidArgumentException::class);
});

it('rejects values that overflow the requested bitCount', function (): void {
    $buf = new BitBuffer();
    expect(fn () => $buf->appendBits(8, 3))->toThrow(InvalidArgumentException::class);
    expect(fn () => $buf->appendBits(-1, 3))->toThrow(InvalidArgumentException::class);
});

it('treats bitCount=0 as a no-op', function (): void {
    $buf = new BitBuffer();
    $buf->appendBits(0, 0);
    expect($buf->size())->toBe(0);
});

it('packs multiple appends into bytes', function (): void {
    $buf = new BitBuffer();
    $buf->appendBits(0b0010, 4);
    $buf->appendBits(0b000001011, 9);
    // 0010 + 000001011 = 0010 0000 0101 1 (13 bits)
    // Padded: 0010 0000 0101 1000 → bytes 0x20, 0x58
    expect($buf->toBytes())->toBe([0x20, 0x58]);
});
