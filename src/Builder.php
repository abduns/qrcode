<?php

declare(strict_types=1);

namespace Dunn\QrCode;

use Dunn\QrCode\Encoder\BlockInterleaver;
use Dunn\QrCode\Encoder\DataEncoder;
use Dunn\QrCode\Encoder\Mode;
use Dunn\QrCode\ErrorCorrection\GeneratorPolynomial;
use Dunn\QrCode\ErrorCorrection\ReedSolomon;
use Dunn\QrCode\Mask\MaskSelector;
use Dunn\QrCode\Math\GaloisField256;
use Dunn\QrCode\Matrix\MatrixBuilder;

/**
 * Fluent, immutable-by-clone builder for {@see QrCode}.
 *
 * Every setter returns a new {@see Builder} instance so the original is
 * unaffected. Call {@see build()} to run the encoding pipeline and produce
 * the immutable result.
 */
final class Builder
{
    private EccLevel $eccLevel = EccLevel::Medium;
    private ?int $forceVersion = null;
    private ?Mode $forceMode = null;

    public function __construct(private readonly string $data)
    {
    }

    public function errorCorrection(EccLevel $level): self
    {
        $clone = clone $this;
        $clone->eccLevel = $level;

        return $clone;
    }

    public function forceVersion(int $version): self
    {
        $clone = clone $this;
        $clone->forceVersion = $version;

        return $clone;
    }

    public function forceMode(Mode $mode): self
    {
        $clone = clone $this;
        $clone->forceMode = $mode;

        return $clone;
    }

    public function build(): QrCode
    {
        $encoded = (new DataEncoder())->encode(
            $this->data,
            $this->eccLevel,
            $this->forceVersion,
            $this->forceMode,
        );

        $gf = new GaloisField256();
        $rs = new ReedSolomon($gf, new GeneratorPolynomial($gf));
        $interleaver = new BlockInterleaver($rs);
        $stream = $interleaver->interleave(
            $encoded->codewords,
            $encoded->version,
            $encoded->eccLevel,
        );

        $unmasked = (new MatrixBuilder())->build($encoded->version, $stream);
        [$masked, $mask] = (new MaskSelector())->selectAndApply($unmasked, $encoded->eccLevel);

        return new QrCode(
            matrix: $masked,
            version: $encoded->version,
            eccLevel: $encoded->eccLevel,
            mode: $encoded->mode,
            maskPattern: $mask,
        );
    }
}
