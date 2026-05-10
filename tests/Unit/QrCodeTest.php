<?php

declare(strict_types=1);

use Dunn\QrCode\Builder;
use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\Mask\MaskPattern;
use Dunn\QrCode\QrCode;

it('returns a Builder from QrCode::create()', function (): void {
    expect(QrCode::create('hello'))->toBeInstanceOf(Builder::class);
});

it('builds a V1-M alphanumeric QR for "HELLO WORLD" end-to-end', function (): void {
    $qr = QrCode::create('HELLO WORLD')
        ->errorCorrection(EccLevel::Medium)
        ->build();

    expect($qr)->toBeInstanceOf(QrCode::class);
    expect($qr->version)->toBe(1);
    expect($qr->size())->toBe(21);
    expect($qr->eccLevel)->toBe(EccLevel::Medium);
    expect($qr->mode)->toBe(Mode::Alphanumeric);
    expect($qr->maskPattern)->toBeInstanceOf(MaskPattern::class);
});

it('defaults to ECC Medium when errorCorrection() is not called', function (): void {
    $qr = QrCode::create('HELLO WORLD')->build();
    expect($qr->eccLevel)->toBe(EccLevel::Medium);
});

it('respects errorCorrection() and bumps version to fit at higher ECC levels', function (): void {
    $low = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Low)->build();
    $high = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::High)->build();

    expect($low->eccLevel)->toBe(EccLevel::Low);
    expect($high->eccLevel)->toBe(EccLevel::High);
    // V1-H holds 9 codewords; HELLO WORLD alphanumeric needs more, so V2-H is used.
    expect($high->version)->toBeGreaterThanOrEqual($low->version);
});

it('respects forceVersion()', function (): void {
    $qr = QrCode::create('HI')->forceVersion(5)->build();
    expect($qr->version)->toBe(5);
    expect($qr->size())->toBe(37);
});

it('respects forceMode()', function (): void {
    $qr = QrCode::create('123')->forceMode(Mode::Byte)->build();
    expect($qr->mode)->toBe(Mode::Byte);
});

it('builder setters are immutable: each returns a new instance', function (): void {
    $b1 = QrCode::create('hi');
    $b2 = $b1->errorCorrection(EccLevel::High);

    expect($b1)->not->toBe($b2);

    // The original builder still uses the default ECC.
    expect($b1->build()->eccLevel)->toBe(EccLevel::Medium);
    expect($b2->build()->eccLevel)->toBe(EccLevel::High);
});

it('produces a masked matrix with format-info bits written', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();

    // Verify some format-info cell is set: by definition, after MaskSelector
    // runs at least one of the 15 format-info positions is dark for any
    // (ECC, mask) pair (the encoded bits aren't all zero).
    $atLeastOneDark = false;
    for ($c = 0; $c <= 8; $c++) {
        if ($c !== 6 && $qr->matrix->get(8, $c)) {
            $atLeastOneDark = true;
            break;
        }
    }
    expect($atLeastOneDark)->toBeTrue();
});

it('runs the full pipeline across versions 1, 5, 10, 20', function (): void {
    foreach ([1, 5, 10, 20] as $v) {
        foreach (EccLevel::cases() as $ecc) {
            $qr = QrCode::create('TEST')->errorCorrection($ecc)->forceVersion($v)->build();
            expect($qr->version)->toBe($v);
            expect($qr->size())->toBe(4 * $v + 17);
        }
    }
});
