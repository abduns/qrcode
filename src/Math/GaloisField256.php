<?php

declare(strict_types=1);

namespace Dunn\QrCode\Math;

use InvalidArgumentException;

/**
 * GF(2^8) arithmetic with primitive polynomial 0x11D (x^8 + x^4 + x^3 + x^2 + 1)
 * and primitive element α = 2.
 *
 * Used by Reed–Solomon error correction. All public methods accept and return
 * byte values (0..255). Addition and subtraction are XOR; multiplication and
 * division go through precomputed log/antilog tables.
 */
final class GaloisField256
{
    private const PRIMITIVE = 0x11D;

    /** @var array<int, int> exp[i] = α^i for i in 0..254 (and exp[255] = 1 for convenience) */
    private array $exp;

    /** @var array<int, int> log[exp[i]] = i for i in 0..254. log[0] is undefined; stored as 0. */
    private array $log;

    public function __construct()
    {
        $exp = array_fill(0, 256, 0);
        $log = array_fill(0, 256, 0);

        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;

            $x <<= 1;
            if (($x & 0x100) !== 0) {
                $x ^= self::PRIMITIVE;
            }
        }
        $exp[255] = $exp[0];

        $this->exp = $exp;
        $this->log = $log;
    }

    public function multiply(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }

        return $this->exp[($this->log[$a] + $this->log[$b]) % 255];
    }

    public function divide(int $a, int $b): int
    {
        if ($b === 0) {
            throw new InvalidArgumentException('Division by zero in GF(256).');
        }
        if ($a === 0) {
            return 0;
        }

        return $this->exp[($this->log[$a] - $this->log[$b] + 255) % 255];
    }

    /**
     * α raised to the given power. Accepts any integer (negative wraps cyclically).
     */
    public function exp(int $i): int
    {
        $i = (($i % 255) + 255) % 255;

        return $this->exp[$i];
    }

    /**
     * Discrete log (base α). Undefined for 0.
     */
    public function log(int $a): int
    {
        if ($a === 0) {
            throw new InvalidArgumentException('log(0) is undefined in GF(256).');
        }

        return $this->log[$a];
    }
}
