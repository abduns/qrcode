<?php

declare(strict_types=1);

use Dunn\QrCode\Style\Color;
use Dunn\QrCode\Style\Gradient\GradientStop;
use Dunn\QrCode\Style\Gradient\LinearGradient;
use Dunn\QrCode\Style\Gradient\RadialGradient;

it('GradientStop rejects offsets outside 0..1', function (): void {
    expect(fn () => new GradientStop(-0.1, Color::black()))->toThrow(InvalidArgumentException::class);
    expect(fn () => new GradientStop(1.5, Color::black()))->toThrow(InvalidArgumentException::class);
});

it('LinearGradient requires at least two stops', function (): void {
    expect(fn () => new LinearGradient([new GradientStop(0.0, Color::black())]))
        ->toThrow(InvalidArgumentException::class);
});

it('LinearGradient emits a <linearGradient> defs fragment with stops', function (): void {
    $grad = new LinearGradient(
        [
            new GradientStop(0.0, Color::hex('#264653')),
            new GradientStop(1.0, Color::hex('#2a9d8f')),
        ],
        x1: 0,
        y1: 0,
        x2: 1,
        y2: 1,
    );

    $fragment = $grad->defsFragment('my-grad');

    expect($fragment)->toStartWith('<linearGradient id="my-grad"');
    expect($fragment)->toContain('x1="0" y1="0" x2="1" y2="1"');
    expect($fragment)->toContain('<stop offset="0" stop-color="#264653"');
    expect($fragment)->toContain('<stop offset="1" stop-color="#2a9d8f"');
    expect($fragment)->toEndWith('</linearGradient>');
});

it('LinearGradient defaults to top-left → bottom-right (0,0 → 1,1)', function (): void {
    $grad = new LinearGradient([
        new GradientStop(0.0, Color::black()),
        new GradientStop(1.0, Color::white()),
    ]);

    expect($grad->defsFragment('g'))->toContain('x1="0" y1="0" x2="1" y2="1"');
});

it('RadialGradient emits a <radialGradient> defs fragment with stops', function (): void {
    $grad = new RadialGradient(
        [
            new GradientStop(0.0, Color::hex('#e76f51')),
            new GradientStop(1.0, Color::hex('#264653')),
        ],
        cx: 0.5,
        cy: 0.5,
        r: 0.7,
    );

    $fragment = $grad->defsFragment('r1');

    expect($fragment)->toStartWith('<radialGradient id="r1"');
    expect($fragment)->toContain('cx="0.5" cy="0.5" r="0.7"');
    expect($fragment)->toContain('<stop offset="0" stop-color="#e76f51"');
    expect($fragment)->toContain('<stop offset="1" stop-color="#264653"');
});

it('RadialGradient rejects non-positive radius', function (): void {
    expect(fn () => new RadialGradient([
        new GradientStop(0.0, Color::black()),
        new GradientStop(1.0, Color::white()),
    ], r: 0))->toThrow(InvalidArgumentException::class);
});

it('emits stop-opacity for RGBA stops', function (): void {
    $grad = new LinearGradient([
        new GradientStop(0.0, Color::rgba(255, 0, 0, 0.5)),
        new GradientStop(1.0, Color::rgba(0, 0, 255, 1.0)),
    ]);

    expect($grad->defsFragment('g'))->toContain('stop-opacity="0.5"');
});
