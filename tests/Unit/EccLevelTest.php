<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;

it('emits the non-monotonic format-info bit pattern from ISO 18004 Table 12', function (): void {
    expect(EccLevel::Low->formatBits())->toBe(0b01);
    expect(EccLevel::Medium->formatBits())->toBe(0b00);
    expect(EccLevel::Quartile->formatBits())->toBe(0b11);
    expect(EccLevel::High->formatBits())->toBe(0b10);
});
