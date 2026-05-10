<?php

declare(strict_types=1);

use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\Encoder\ModeDetector;

it('picks Numeric for digits-only input', function (): void {
    expect((new ModeDetector())->detect('12345'))->toBe(Mode::Numeric);
});

it('picks Alphanumeric for input within the 45-char set', function (): void {
    expect((new ModeDetector())->detect('HELLO WORLD'))->toBe(Mode::Alphanumeric);
    expect((new ModeDetector())->detect('A1:B2'))->toBe(Mode::Alphanumeric);
});

it('picks Byte for any character outside the alphanumeric set', function (): void {
    expect((new ModeDetector())->detect('Hello, World!'))->toBe(Mode::Byte);
    expect((new ModeDetector())->detect('lowercase'))->toBe(Mode::Byte);
});

it('falls back to Byte for the empty string', function (): void {
    expect((new ModeDetector())->detect(''))->toBe(Mode::Byte);
});
