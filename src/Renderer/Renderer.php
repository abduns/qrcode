<?php

declare(strict_types=1);

namespace Dunn\QrCode\Renderer;

use Dunn\QrCode\QrCode;

/**
 * Strategy interface for rendering a {@see QrCode} to a serialized form.
 */
interface Renderer
{
    /**
     * Render the QR code to its serialized form (SVG/PNG bytes/text).
     */
    public function render(QrCode $qr): string;

    /**
     * The MIME type of the serialized output, suitable for HTTP headers.
     */
    public function mimeType(): string;
}
