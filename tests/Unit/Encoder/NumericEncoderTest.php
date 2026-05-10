<?php

declare(strict_types=1);

use Dunn\QrCode\Encoder\BitBuffer;
use Dunn\QrCode\Encoder\NumericEncoder;

it('encodes "8675309" per ISO 18004 worked example', function (): void {
    $buf = new BitBuffer();
    (new NumericEncoder())->encode('8675309', $buf);

    // 867 → 1101100011 (10 bits), 530 → 1000010010 (10 bits), 9 → 1001 (4 bits)
    expect(implode('', $buf->getBits()))->toBe('1101100011' . '1000010010' . '1001');
    expect($buf->size())->toBe(24);
});

it('encodes a 6-digit string into two 10-bit groups', function (): void {
    $buf = new BitBuffer();
    (new NumericEncoder())->encode('123456', $buf);

    // 123 → 0001111011 (10 bits), 456 → 0111001000 (10 bits)
    expect(implode('', $buf->getBits()))->toBe('0001111011' . '0111001000');
});

it('encodes a single trailing digit into 4 bits', function (): void {
    $buf = new BitBuffer();
    (new NumericEncoder())->encode('1', $buf);
    expect(implode('', $buf->getBits()))->toBe('0001');
});

it('encodes two trailing digits into 7 bits', function (): void {
    $buf = new BitBuffer();
    (new NumericEncoder())->encode('42', $buf);
    expect(implode('', $buf->getBits()))->toBe('0101010');
});

it('rejects non-digit input', function (): void {
    $buf = new BitBuffer();
    expect(fn () => (new NumericEncoder())->encode('12a', $buf))
        ->toThrow(InvalidArgumentException::class);
});

it('treats empty input as a no-op', function (): void {
    $buf = new BitBuffer();
    (new NumericEncoder())->encode('', $buf);
    expect($buf->size())->toBe(0);
});
