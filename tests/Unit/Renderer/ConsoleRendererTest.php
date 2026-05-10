<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Console\ConsoleRenderer;

it('produces (size + 2*margin) lines of (size + 2*margin) doubled glyphs', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
    $rendered = (new ConsoleRenderer(margin: 2))->render($qr);

    // V1 is 21 modules; with margin 2 we expect 25 lines.
    $lines = explode("\n", rtrim($rendered, "\n"));
    expect($lines)->toHaveCount(25);

    // Each line uses 2-char glyphs over (size + 2*margin) = 25 modules → 50 chars by mb_strlen.
    foreach ($lines as $line) {
        expect(mb_strlen($line))->toBe(50);
    }
});

it('reports MIME type text/plain', function (): void {
    expect((new ConsoleRenderer())->mimeType())->toBe('text/plain');
});

it('renders top quiet zone as all-light glyphs', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
    $rendered = (new ConsoleRenderer(margin: 2, lightGlyph: '..'))->render($qr);

    $lines = explode("\n", $rendered);
    // First two lines should be entirely light.
    expect($lines[0])->toBe(str_repeat('..', 25));
    expect($lines[1])->toBe(str_repeat('..', 25));
});

it('uses configurable glyphs', function (): void {
    $qr = QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
    $rendered = (new ConsoleRenderer(margin: 0, darkGlyph: 'X', lightGlyph: '.'))->render($qr);

    expect($rendered)->toContain('X');
    expect($rendered)->toContain('.');
});
