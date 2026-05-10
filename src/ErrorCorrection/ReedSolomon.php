<?php

declare(strict_types=1);

namespace Dunn\QrCode\ErrorCorrection;

use Dunn\QrCode\Math\GaloisField256;

/**
 * Reed–Solomon encoder over GF(256).
 *
 * Performs polynomial long division of (data * x^eccCount) by the generator
 * polynomial of degree eccCount; the remainder is the ECC.
 */
final class ReedSolomon
{
    public function __construct(
        private readonly GaloisField256 $gf,
        private readonly GeneratorPolynomial $generators,
    ) {
    }

    /**
     * @param list<int> $data Data codewords, each 0..255.
     * @param int $eccCount   Number of ECC codewords to produce.
     * @return list<int>      ECC codewords, length = $eccCount.
     */
    public function encode(array $data, int $eccCount): array
    {
        if ($eccCount < 0) {
            throw new \InvalidArgumentException('ECC count must be non-negative.');
        }
        if ($eccCount === 0) {
            return [];
        }

        $generator = $this->generators->forDegree($eccCount);
        $genLen = count($generator);
        $dataLen = count($data);

        $buf = $data;
        for ($i = 0; $i < $eccCount; $i++) {
            $buf[] = 0;
        }

        for ($i = 0; $i < $dataLen; $i++) {
            $coef = $buf[$i];
            if ($coef === 0) {
                continue;
            }
            for ($j = 0; $j < $genLen; $j++) {
                $buf[$i + $j] ^= $this->gf->multiply($coef, $generator[$j]);
            }
        }

        return array_slice($buf, $dataLen);
    }
}
