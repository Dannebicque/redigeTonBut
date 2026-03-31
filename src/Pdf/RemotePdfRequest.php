<?php


namespace App\Pdf;

final class RemotePdfRequest
{
    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly string $type,
        public readonly array  $options,
        public readonly array  $payload,
        public readonly string $filename,
        public readonly string $sourceHash,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiPayload(string $jobId, string $cacheKey, string $callbackUrl): array
    {
        return [
            'type' => $this->type,
            'jobId' => $jobId,
            'cacheKey' => $cacheKey,
            'callbackUrl' => $callbackUrl,
            'options' => $this->options,
            'payload' => $this->payload,
        ];
    }
}
