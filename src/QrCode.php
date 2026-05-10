<?php

declare(strict_types=1);

namespace Dunn\QrCode;

/**
 * Public entry point for the QR Code library.
 *
 * The full fluent builder + immutable value-object API lands in Phase 5 of
 * the development plan. This skeleton exists so the package is installable
 * and CI is green from commit one.
 */
final class QrCode
{
    private function __construct(
        private readonly string $data,
    ) {
    }

    public static function create(string $data): self
    {
        return new self($data);
    }

    public function getData(): string
    {
        return $this->data;
    }
}
