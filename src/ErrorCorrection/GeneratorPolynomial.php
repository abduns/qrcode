<?php

declare(strict_types=1);

namespace Dunn\QrCode\ErrorCorrection;

use Dunn\QrCode\Math\GaloisField256;

/**
 * Builds and caches Reed–Solomon generator polynomials over GF(256).
 *
 *   g(x) = ∏_{i=0..degree-1} (x + α^i)
 *
 * Coefficients are returned highest-degree-first, with leading 1.
 */
final class GeneratorPolynomial
{
    /** @var array<int, list<int>> degree => coefficients [1, g_{d-1}, ..., g_0] */
    private array $cache = [];

    public function __construct(private readonly GaloisField256 $gf)
    {
    }

    /**
     * @return list<int> Coefficients highest-degree-first, length degree+1, leading coefficient 1.
     */
    public function forDegree(int $degree): array
    {
        if ($degree < 0) {
            throw new \InvalidArgumentException('Generator polynomial degree must be non-negative.');
        }

        if (isset($this->cache[$degree])) {
            return $this->cache[$degree];
        }

        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $poly = $this->multiplyByLinear($poly, $this->gf->exp($i));
        }

        return $this->cache[$degree] = $poly;
    }

    /**
     * Multiply P(x) by (x + c) in GF(256), preserving the highest-degree-first layout.
     *
     * @param list<int> $poly
     * @return list<int>
     */
    private function multiplyByLinear(array $poly, int $c): array
    {
        $n = count($poly);
        $result = [];
        $result[] = $poly[0];
        for ($j = 1; $j < $n; $j++) {
            $result[] = $poly[$j] ^ $this->gf->multiply($c, $poly[$j - 1]);
        }
        $result[] = $this->gf->multiply($c, $poly[$n - 1]);

        return $result;
    }
}
