<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\QrCode;
use Dunn\QrCode\Renderer\Svg\SvgRenderer;
use Dunn\QrCode\Style\EyeStyle\CircleEye;
use Dunn\QrCode\Style\ModuleShape\DotModule;

function styledQr(): QrCode
{
    return QrCode::create('HELLO WORLD')->errorCorrection(EccLevel::Medium)->build();
}

it('falls back to SquareModule + SquareEye when no styles are passed', function (): void {
    $svg = (new SvgRenderer())->render(styledQr());

    // Square module path fragment should appear (per-module h1 v1).
    expect($svg)->toContain('h1v1h-1z');
    // Square eye path fragment should appear.
    expect($svg)->toContain('h7v7h-7z');
    // crispEdges rendering hint.
    expect($svg)->toContain('shape-rendering="crispEdges"');
});

it('uses dot modules when DotModule is passed', function (): void {
    $svg = (new SvgRenderer(moduleShape: new DotModule()))->render(styledQr());

    expect($svg)->toContain('a.5 .5');                          // dot circle arc
    expect($svg)->toContain('shape-rendering="geometricPrecision"');
    expect($svg)->toContain('h7v7h-7z');                        // square eye still
});

it('uses circle eyes when CircleEye is passed', function (): void {
    $svg = (new SvgRenderer(eyeStyle: new CircleEye()))->render(styledQr());

    expect($svg)->toContain('a3.5 3.5');                        // outer circle
    expect($svg)->toContain('a2.5 2.5');                        // hole
    expect($svg)->toContain('a1.5 1.5');                        // inner dot
    expect($svg)->toContain('shape-rendering="geometricPrecision"');
});

it('combines DotModule + CircleEye into a fully circular QR', function (): void {
    $svg = (new SvgRenderer(
        moduleShape: new DotModule(),
        eyeStyle: new CircleEye(),
    ))->render(styledQr());

    expect($svg)->toContain('a.5 .5');     // dot module arcs
    expect($svg)->toContain('a3.5 3.5');   // circle eye outer
    // No square module/eye paths.
    expect($svg)->not->toContain('h1v1h-1z');
    expect($svg)->not->toContain('h7v7h-7z');
});

it('keeps default Square+Square output reasonably small (<5KB) for V1-M', function (): void {
    $svg = (new SvgRenderer())->render(styledQr());
    expect(strlen($svg))->toBeLessThan(5000);
});

it('uses fill-rule="evenodd" so eye holes render correctly', function (): void {
    $svg = (new SvgRenderer())->render(styledQr());
    expect($svg)->toContain('fill-rule="evenodd"');
});
