<?php

namespace App\Pdf;

final class PdfPayloadBuilderRegistry
{
    /**
     * @param iterable<PdfPayloadBuilderInterface> $builders
     */
    public function __construct(
        private readonly iterable $builders,
    ) {
    }

    public function getBuilder(PdfSourceType $sourceType, string $documentKey): PdfPayloadBuilderInterface
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($sourceType, $documentKey)) {
                return $builder;
            }
        }

        throw new \RuntimeException(sprintf(
            'Aucun builder PDF trouvé pour %s / %s.',
            $sourceType->value,
            $documentKey
        ));
    }
}
