<?php

namespace App\Pdf;

final class PdfParametersNormalizer
{
    public function normalize(array $parameters): array
    {
        $copy = $parameters;
        $this->sortRecursive($copy);

        return $copy;
    }

    public function hash(array $parameters): string
    {
        $normalized = $this->normalize($parameters);

        return hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function sortRecursive(array &$array): void
    {
        ksort($array);

        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->sortRecursive($value);
            }
        }
    }
}
