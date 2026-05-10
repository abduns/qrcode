<?php

declare(strict_types=1);

use Dunn\QrCode\Encoder\AlphanumericEncoder;
use Dunn\QrCode\Encoder\BitBuffer;

it('encodes "HELLO WORLD" per ISO 18004 Annex I', function (): void {
    $buf = new BitBuffer();
    (new AlphanumericEncoder())->encode('HELLO WORLD', $buf);

    // 11 chars → 5 pairs (11 bits each) + 1 single (6 bits) = 61 bits
    expect($buf->size())->toBe(61);
    expect(implode('', $buf->getBits()))->toBe(
        '01100001011' . '01111000110' . '10001011100'
        . '10110111000' . '10011010100' . '001101'
    );
});

it('encodes a single character into 6 bits', function (): void {
    $buf = new BitBuffer();
    (new AlphanumericEncoder())->encode('A', $buf);
    // 'A' = 10 → 6-bit binary 001010
    expect(implode('', $buf->getBits()))->toBe('001010');
});

it('encodes a pair into 11 bits', function (): void {
    $buf = new BitBuffer();
    (new AlphanumericEncoder())->encode('AB', $buf);
    // A=10, B=11 → 10*45 + 11 = 461 → 11-bit binary 00111001101
    expect(implode('', $buf->getBits()))->toBe('00111001101');
});

it('rejects characters outside the 45-char alphanumeric set', function (): void {
    $buf = new BitBuffer();
    expect(fn () => (new AlphanumericEncoder())->encode('a', $buf))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => (new AlphanumericEncoder())->encode('Hello', $buf))
        ->toThrow(InvalidArgumentException::class);
});
