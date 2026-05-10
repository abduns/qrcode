<?php

declare(strict_types=1);

use Dunn\QrCode\Style\Logo;

function tinyPngBytes(): string
{
    return base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
    );
}

it('accepts a supported MIME type and non-empty bytes', function (): void {
    $logo = new Logo(tinyPngBytes(), 'image/png', sizeRatio: 0.2);
    expect($logo->mimeType)->toBe('image/png');
    expect($logo->sizeRatio)->toBe(0.2);
    expect($logo->clearBackground)->toBeTrue();
});

it('rejects an unsupported MIME type', function (): void {
    expect(fn () => new Logo('xyz', 'image/bmp'))->toThrow(InvalidArgumentException::class);
});

it('rejects empty bytes', function (): void {
    expect(fn () => new Logo('', 'image/png'))->toThrow(InvalidArgumentException::class);
});

it('rejects out-of-range sizeRatio', function (): void {
    expect(fn () => new Logo(tinyPngBytes(), 'image/png', sizeRatio: 0))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => new Logo(tinyPngBytes(), 'image/png', sizeRatio: 0.6))
        ->toThrow(InvalidArgumentException::class);
});

it('produces a data: URI with base64 payload', function (): void {
    $logo = new Logo(tinyPngBytes(), 'image/png');
    $uri = $logo->dataUri();

    expect($uri)->toStartWith('data:image/png;base64,');
    expect(base64_decode(substr($uri, 22)))->toBe(tinyPngBytes());
});

it('loads from a file with auto-detected MIME', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'logo').'.png';
    file_put_contents($tmp, tinyPngBytes());

    try {
        $logo = Logo::fromFile($tmp, sizeRatio: 0.15);
        expect($logo->mimeType)->toBe('image/png');
        expect($logo->sizeRatio)->toBe(0.15);
        expect($logo->bytes)->toBe(tinyPngBytes());
    } finally {
        @unlink($tmp);
    }
});

it('rejects fromFile with an unsupported extension', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'logo').'.bmp';
    file_put_contents($tmp, 'xyz');

    try {
        expect(fn () => Logo::fromFile($tmp))->toThrow(InvalidArgumentException::class);
    } finally {
        @unlink($tmp);
    }
});

it('rejects fromFile when the file is missing', function (): void {
    expect(fn () => Logo::fromFile('/no/such/file.png'))
        ->toThrow(InvalidArgumentException::class);
});
