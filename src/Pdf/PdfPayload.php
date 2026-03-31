<?php

namespace App\Pdf;

final readonly class PdfPayload
{
    public function __construct(
        public string $kind, // html|latex
        public array  $payload,
        public array  $options,
        public string $sourceHash,
        public string $filename,
    )
    {
    }
}
