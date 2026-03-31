<?php

namespace App\Pdf;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PdfRemoteClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
        private readonly string $apiToken,
        private readonly HmacSigner $signer,
    ) {
    }

    public function createJob(array $payload): void
    {
        $raw = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($raw === false) {
            throw new \RuntimeException('Impossible d’encoder la requête PDF.');
        }

        $this->httpClient->request('POST', rtrim($this->baseUrl, '/').'/api/jobs', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiToken,
                'Content-Type' => 'application/json',
                'X-Signature' => $this->signer->sign($raw),
            ],
            'body' => $raw,
            'timeout' => 30,
        ])->getContent();
    }
}
