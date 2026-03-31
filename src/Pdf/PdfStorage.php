<?php

namespace App\Pdf;

use Symfony\Component\Filesystem\Filesystem;

final class PdfStorage
{
    private Filesystem $filesystem;

    public function __construct(private readonly string $storageDir)
    {
        $this->filesystem = new Filesystem();
    }

    public function createNewPath(string $sourceType, string $sourceId, string $documentKey, string $filename): string
    {
        $dir = sprintf(
            '%s/%s/%s/%s',
            rtrim($this->storageDir, '/'),
            $sourceType,
            $sourceId,
            $documentKey
        );

        $this->filesystem->mkdir($dir);

        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $filename) ?: 'document.pdf';

        return $dir.'/'.uniqid('pdf_', true).'_'.$safeFilename;
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path && is_file($path)) {
            $this->filesystem->remove($path);
        }
    }
}
