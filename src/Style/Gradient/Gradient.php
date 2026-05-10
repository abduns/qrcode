<?php

declare(strict_types=1);

namespace Dunn\QrCode\Style\Gradient;

/**
 * SVG gradient that can be used in place of a {@see \Dunn\QrCode\Style\Color}
 * anywhere `SvgRenderer` accepts a colour parameter.
 *
 * Implementations return the `<linearGradient>` / `<radialGradient>` SVG
 * fragment to embed in `<defs>`, plus their referenceable id is used by the
 * renderer to build `fill="url(#…)"` attributes.
 */
interface Gradient
{
    /**
     * Render the gradient as an SVG `<defs>` element with the given id.
     */
    public function defsFragment(string $id): string;
}
