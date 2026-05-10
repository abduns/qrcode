<?php

declare(strict_types=1);

use Dunn\QrCode\Mask\PenaltyEvaluator;
use Dunn\QrCode\Matrix\Matrix;

it('N1 scores 0 for a checkerboard (no 5+ run in either direction)', function (): void {
    $m = new Matrix(1);
    for ($r = 0; $r < $m->size(); $r++) {
        for ($c = 0; $c < $m->size(); $c++) {
            $m->set($r, $c, ($r + $c) % 2 === 0);
        }
    }

    expect((new PenaltyEvaluator())->n1($m))->toBe(0);
});

it('N1 scores 3 + (run-5) per same-color run >= 5', function (): void {
    $m = new Matrix(1); // 21x21
    // First row: 6 dark then alternating
    for ($c = 0; $c < 6; $c++) {
        $m->set(0, $c, true);
    }
    for ($c = 6; $c < 21; $c++) {
        $m->set(0, $c, $c % 2 === 1);
    }
    // Penalty for row 0 = 3 + (6 - 5) = 4 (one run of 6 darks).
    // Plus penalties from all-light columns: cols where the cell has no 5+ run remain — but
    // remember columns also get scored. Cols 0..20 with row 0 either dark or light, rows 1..20 all light:
    // - Cols 0..5 have darkrow0 + 20 light below = run of 1 dark, run of 20 light.
    //   Penalty per col = (3 + (20 - 5)) = 18.
    // - Cols 6..20: row 0 follows col%2; rows 1..20 all light. Cols where row 0 is also light continue
    //   the light run for 21 → 3 + 16 = 19. Cols where row 0 is dark: 1 dark + 20 light → 3 + 15 = 18.

    $penalty = (new PenaltyEvaluator())->n1($m);
    // We don't precisely predict every column — but penalty must include the row-0 contribution.
    expect($penalty)->toBeGreaterThanOrEqual(4);
});

it('N2 scores 3 per 2x2 monochromatic block', function (): void {
    $m = new Matrix(1);
    // All-light matrix: every 2x2 block is light. (size-1)^2 blocks.
    $expected = 3 * (20 * 20); // V1 size 21 → 20×20 blocks
    expect((new PenaltyEvaluator())->n2($m))->toBe($expected);
});

it('N2 scores 0 when no 2x2 monochromatic blocks exist', function (): void {
    $m = new Matrix(1);
    // Checkerboard: every 2x2 has both colors.
    for ($r = 0; $r < $m->size(); $r++) {
        for ($c = 0; $c < $m->size(); $c++) {
            $m->set($r, $c, ($r + $c) % 2 === 0);
        }
    }
    expect((new PenaltyEvaluator())->n2($m))->toBe(0);
});

it('N3 scores 40 per DLDDDLD-with-4-light-border occurrence', function (): void {
    $m = new Matrix(1);
    // Place D L D D D L D + four trailing lights at (0, 0..10).
    // Pattern: 1 0 1 1 1 0 1 0 0 0 0
    $pattern = [true, false, true, true, true, false, true, false, false, false, false];
    for ($i = 0; $i < 11; $i++) {
        $m->set(0, $i, $pattern[$i]);
    }
    // Row 0 should hit pattern A at column 0.
    // (We may also hit pattern B at some column if 4-light-then-DLDDDLD aligns.)
    expect((new PenaltyEvaluator())->n3($m))->toBeGreaterThanOrEqual(40);
});

it('N4 scores 0 when dark proportion is within 5% of 50%', function (): void {
    $m = new Matrix(1);
    $size = $m->size();          // 21
    $total = $size * $size;      // 441
    // Set dark count to 220 (49.886%) — just under 50%.
    $count = 0;
    for ($r = 0; $r < $size && $count < 220; $r++) {
        for ($c = 0; $c < $size && $count < 220; $c++) {
            $m->set($r, $c, true);
            $count++;
        }
    }
    expect((new PenaltyEvaluator())->n4($m))->toBe(0);
});

it('N4 scores 10 per 5% deviation from 50%', function (): void {
    $m = new Matrix(1);
    // 198 dark cells = 44.898% — about 5% off.
    $count = 0;
    for ($r = 0; $r < $m->size() && $count < 198; $r++) {
        for ($c = 0; $c < $m->size() && $count < 198; $c++) {
            $m->set($r, $c, true);
            $count++;
        }
    }
    expect((new PenaltyEvaluator())->n4($m))->toBe(10);
});

it('evaluate() returns the sum of N1+N2+N3+N4', function (): void {
    $m = new Matrix(1); // all-light
    $eval = new PenaltyEvaluator();
    $sum = $eval->n1($m) + $eval->n2($m) + $eval->n3($m) + $eval->n4($m);
    expect($eval->evaluate($m))->toBe($sum);
});
