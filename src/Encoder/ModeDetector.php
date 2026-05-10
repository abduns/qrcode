<?php

declare(strict_types=1);

namespace Dunn\QrCode\Encoder;

/**
 * Pick the smallest single mode that can encode the entire input.
 *
 * Optimal mixed-mode segmentation is a stretch goal post-v1.
 */
final class ModeDetector
{
    public function detect(string $data): Mode
    {
        if ($data === '') {
            return Mode::Byte;
        }
        if (strspn($data, '0123456789') === strlen($data)) {
            return Mode::Numeric;
        }
        if (strspn($data, AlphanumericEncoder::CHARSET) === strlen($data)) {
            return Mode::Alphanumeric;
        }

        return Mode::Byte;
    }
}
