<?php

declare(strict_types=1);

use Dunn\QrCode\Style\Color;

it('parses 6-character hex (with or without leading #)', function (): void {
    expect(Color::hex('#1a1a2e')->toHex())->toBe('#1a1a2e');
    expect(Color::hex('1a1a2e')->toHex())->toBe('#1a1a2e');
});

it('expands 3-character hex shorthand', function (): void {
    expect(Color::hex('#abc'))->toEqual(Color::rgb(0xaa, 0xbb, 0xcc));
});

it('rejects invalid hex inputs', function (): void {
    expect(fn () => Color::hex('not-a-color'))->toThrow(InvalidArgumentException::class);
    expect(fn () => Color::hex('#12'))->toThrow(InvalidArgumentException::class);
    expect(fn () => Color::hex('#gghhii'))->toThrow(InvalidArgumentException::class);
});

it('rejects out-of-range RGB channels', function (): void {
    expect(fn () => new Color(-1, 0, 0))->toThrow(InvalidArgumentException::class);
    expect(fn () => new Color(0, 256, 0))->toThrow(InvalidArgumentException::class);
});

it('rejects out-of-range alpha', function (): void {
    expect(fn () => new Color(0, 0, 0, -0.1))->toThrow(InvalidArgumentException::class);
    expect(fn () => new Color(0, 0, 0, 1.1))->toThrow(InvalidArgumentException::class);
});

it('produces canonical hex strings', function (): void {
    expect(Color::black()->toHex())->toBe('#000000');
    expect(Color::white()->toHex())->toBe('#ffffff');
    expect(Color::rgb(1, 2, 3)->toHex())->toBe('#010203');
});

it('renders css as hex when alpha == 1', function (): void {
    expect(Color::rgb(10, 20, 30)->toCss())->toBe('#0a141e');
});

it('renders css as rgba(...) when alpha < 1', function (): void {
    $c = Color::rgba(10, 20, 30, 0.5);
    expect($c->toCss())->toBe('rgba(10,20,30,0.5)');
});

it('exposes immutable channels', function (): void {
    $c = new Color(1, 2, 3, 0.5);
    expect($c->r)->toBe(1);
    expect($c->g)->toBe(2);
    expect($c->b)->toBe(3);
    expect($c->alpha)->toBe(0.5);
});
