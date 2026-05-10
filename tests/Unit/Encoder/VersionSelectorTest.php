<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\Encoder\VersionSelector;
use Dunn\QrCode\Exception\DataTooLongException;

it('picks V1 for short alphanumeric input at ECC M', function (): void {
    $v = (new VersionSelector())->selectVersion('HELLO WORLD', Mode::Alphanumeric, EccLevel::Medium);
    expect($v)->toBe(1);
});

it('picks a higher version when data overflows V1 capacity', function (): void {
    $sel = new VersionSelector();
    $longByte = str_repeat('a', 30); // 30 bytes = 240 bits + header > V1-L (152 bits)

    $v = $sel->selectVersion($longByte, Mode::Byte, EccLevel::Low);
    expect($v)->toBeGreaterThan(1);
});

it('throws when even V40-L cannot hold the input', function (): void {
    // V40-L = 2956 codewords = 23648 bits. Need a payload > 23648 - 4 - 16 = 23628 bits.
    // Byte mode: each char = 8 bits, so 2960 chars overflows.
    $tooLong = str_repeat('a', 3000);

    expect(fn () => (new VersionSelector())->selectVersion($tooLong, Mode::Byte, EccLevel::Low))
        ->toThrow(DataTooLongException::class);
});

it('counts payload bits correctly for each mode', function (): void {
    $sel = new VersionSelector();

    expect($sel->payloadBits(Mode::Numeric, '8675309'))->toBe(24);   // 10 + 10 + 4
    expect($sel->payloadBits(Mode::Numeric, '12'))->toBe(7);
    expect($sel->payloadBits(Mode::Numeric, '1'))->toBe(4);
    expect($sel->payloadBits(Mode::Alphanumeric, 'HELLO WORLD'))->toBe(61);
    expect($sel->payloadBits(Mode::Alphanumeric, 'A'))->toBe(6);
    expect($sel->payloadBits(Mode::Byte, 'Hi'))->toBe(16);
});
