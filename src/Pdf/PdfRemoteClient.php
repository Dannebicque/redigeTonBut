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
//        $zip = $payload['payload']['zipBase64'];
//
//        echo "Longueur: " . strlen($zip) . "\n";
//        echo "Est du base64 valide: " . (base64_decode($zip, true) !== false ? 'OUI' : 'NON') . "\n";
//
//// Cherche les caractères non-ASCII
//        preg_match_all('/[^\x00-\x7F]/', $zip, $matches);
//        echo "Chars non-ASCII trouvés: " . count($matches[0]) . "\n";
//        if (!empty($matches[0])) {
//            foreach ($matches[0] as $char) {
//                echo "  -> " . bin2hex($char) . "\n";
//            }
//        }
//        dd($payload);
        $raw = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
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
