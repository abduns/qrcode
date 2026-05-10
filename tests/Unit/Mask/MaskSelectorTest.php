<?php

declare(strict_types=1);

use Dunn\QrCode\EccLevel;
use Dunn\QrCode\Encoder\BlockInterleaver;
use Dunn\QrCode\Encoder\DataEncoder;
use Dunn\QrCode\ErrorCorrection\GeneratorPolynomial;
use Dunn\QrCode\ErrorCorrection\ReedSolomon;
use Dunn\QrCode\Mask\MaskPattern;
use Dunn\QrCode\Mask\MaskSelector;
use Dunn\QrCode\Math\GaloisField256;
use Dunn\QrCode\Matrix\Matrix;
use Dunn\QrCode\Matrix\MatrixBuilder;

function buildHelloWorldUnmasked(): Matrix
{
    $encoded = (new DataEncoder())->encode('HELLO WORLD', EccLevel::Medium);
    $gf = new GaloisField256();
    $rs = new ReedSolomon($gf, new GeneratorPolynomial($gf));
    $stream = (new BlockInterleaver($rs))->interleave($encoded->codewords, 1, EccLevel::Medium);

    return (new MatrixBuilder())->build(1, $stream);
}

it('returns one of the 8 mask patterns for a real QR code', function (): void {
    $unmasked = buildHelloWorldUnmasked();

    [, $mask] = (new MaskSelector())->selectAndApply($unmasked, EccLevel::Medium);

    expect($mask)->toBeInstanceOf(MaskPattern::class);
});

it('does not mutate the input matrix', function (): void {
    $unmasked = buildHelloWorldUnmasked();
    $size = $unmasked->size();

    // Record an arbitrary unreserved data cell before selection.
    $sampleRow = 9;
    $sampleCol = 9;
    $before = $unmasked->get($sampleRow, $sampleCol);

    (new MaskSelector())->selectAndApply($unmasked, EccLevel::Medium);

    // The original matrix is unchanged.
    expect($unmasked->get($sampleRow, $sampleCol))->toBe($before);

    // Also: the format-info cells in the original are still light (only
    // the cloned candidates have format info written).
    expect($unmasked->get(8, 0))->toBeFalse();
    expect($unmasked->get(0, 8))->toBeFalse();
});

it('writes format-info bits into the masked output (V1: cells around top-left finder)', function (): void {
    $unmasked = buildHelloWorldUnmasked();

    [$masked, $mask] = (new MaskSelector())->selectAndApply($unmasked, EccLevel::Medium);

    // The format-info cells are now both reserved AND have meaningful values
    // (some dark, some light). Without checking specific bits, we can verify
    // at least one of them changed from the unmasked baseline.
    $atLeastOneChanged = false;
    for ($c = 0; $c <= 8; $c++) {
        if ($c === 6) {
            continue;
        }
        if ($unmasked->get(8, $c) !== $masked->get(8, $c)) {
            $atLeastOneChanged = true;
            break;
        }
    }
    expect($atLeastOneChanged)->toBeTrue("mask {$mask->value} should have written format info");
});

it('produces a matrix that decodes back to a sensible mask choice (V1-M)', function (): void {
    // For V1-M HELLO WORLD, common reference implementations pick a
    // low-numbered mask. We don't pin a specific value — different penalty
    // implementations agree only loosely — but we verify the chosen mask
    // gives a lower penalty than at least 4 of the others.
    $unmasked = buildHelloWorldUnmasked();
    $selector = new MaskSelector();
    $evaluator = new \Dunn\QrCode\Mask\PenaltyEvaluator();
    $formatInfo = new \Dunn\QrCode\Matrix\FormatInfo();

    [$bestMatrix, $bestMask] = $selector->selectAndApply($unmasked, EccLevel::Medium);
    $bestPenalty = $evaluator->evaluate($bestMatrix);

    $beatenCount = 0;
    foreach (MaskPattern::cases() as $candidate) {
        if ($candidate === $bestMask) {
            continue;
        }
        $clone = clone $unmasked;
        $candidate->applyTo($clone);
        $formatInfo->place($clone, EccLevel::Medium, $candidate->value);
        if ($bestPenalty <= $evaluator->evaluate($clone)) {
            $beatenCount++;
        }
    }
    expect($beatenCount)->toBe(7); // beats all other 7 masks
});
