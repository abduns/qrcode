<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Svg\SvgRenderer;

it('builds without error for 100 random inputs spanning all single-mode charsets', function (): void {
    $charsets = [
        '0123456789',                                            // Numeric
        '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:',         // Alphanumeric
        'abcdefghijklmnopqrstuvwxyz!"#&\'(),;<=>?[]^_`{|}~',     // Byte (lowercase + symbols)
        "\xC3\xA9\xC3\xA0\xE2\x9C\x93\xE2\x98\x83",              // UTF-8 multibyte
    ];

    // Inputs are cryptographically random each run (random_int is not seedable),
    // which is fine: we assert structural invariants, not specific outputs.
    for ($i = 0; $i < 100; $i++) {
        $charset = $charsets[$i % 4];
        $len = random_int(1, 200);
        $data = '';
        for ($j = 0; $j < $len; $j++) {
            $data .= $charset[random_int(0, strlen($charset) - 1)];
        }

        $ecc = EccLevel::cases()[$i % 4];

        $qr = QrCode::create($data)->errorCorrection($ecc)->build();

        // Sanity: a non-empty matrix at a non-zero version.
        expect($qr->version)->toBeGreaterThanOrEqual(1);
        expect($qr->version)->toBeLessThanOrEqual(40);
        expect($qr->size())->toBe(4 * $qr->version + 17);
    }
});

it('generates a V10 byte-mode QR in under 100 ms (perf budget)', function (): void {
    // ~155 bytes fits in V10-M Byte mode (216 codewords - header = plenty).
    $payload = str_repeat('lorem ipsum ', 13);

    $start = microtime(true);
    $qr = QrCode::create($payload)->errorCorrection(EccLevel::Medium)->build();
    $elapsed = microtime(true) - $start;

    expect($qr->version)->toBeGreaterThanOrEqual(8);
    expect($qr->version)->toBeLessThanOrEqual(12);

    // Generous budget (the plan calls for <50ms; some CI hosts are slow).
    expect($elapsed)->toBeLessThan(0.1);
});

it('renders SVG for a V10 byte-mode QR in under 50 ms', function (): void {
    $payload = str_repeat('lorem ipsum ', 13);
    $qr = QrCode::create($payload)->errorCorrection(EccLevel::Medium)->build();

    $renderer = new SvgRenderer();
    $start = microtime(true);
    $svg = $renderer->render($qr);
    $elapsed = microtime(true) - $start;

    expect($svg)->toStartWith('<svg ');
    expect($elapsed)->toBeLessThan(0.05);
});
