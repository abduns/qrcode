<?php

declare(strict_types=1);

use Dunn\QrCode\Exception\PayloadException;
use Dunn\QrCode\Payload\Text;

it('passes text through verbatim', function (): void {
    expect((string) new Text('hello world'))->toBe('hello world');
});

it('preserves whitespace and unicode', function (): void {
    expect((string) new Text("  héllo\nwørld  "))->toBe("  héllo\nwørld  ");
});

it('throws on empty string', function (): void {
    expect(fn () => new Text(''))->toThrow(PayloadException::class, 'text');
});
