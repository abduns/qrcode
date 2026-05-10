<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\ModuleShape;

/**
 * The four cardinal neighbours of a QR module, as a readonly tuple.
 *
 * Renderers compute this for every dark module they emit so that shapes like
 * {@see RoundedModule} can decide which corners to round (corners between two
 * dark neighbours stay square so adjacent modules visually merge).
 */
final readonly class ModuleNeighbours
{
    public function __construct(
        public bool $top,
        public bool $right,
        public bool $bottom,
        public bool $left,
    ) {
    }

    /**
     * All four neighbours absent — the module is fully isolated.
     */
    public static function isolated(): self
    {
        return new self(false, false, false, false);
    }
}
