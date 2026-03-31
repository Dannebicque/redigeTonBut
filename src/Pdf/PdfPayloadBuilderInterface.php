<?php

namespace App\Pdf;

interface PdfPayloadBuilderInterface
{
    public function supports(PdfSourceType $sourceType, string $documentKey): bool;

    public function build(string $sourceId, string $documentKey, array $parameters = []): RemotePdfRequest;
}
