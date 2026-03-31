<?php

namespace App\Pdf;

final class HmacSigner
{
    public function __construct(private readonly string $secret)
    {
    }

    public function sign(string $rawBody): string
    {
        return hash_hmac('sha256', $rawBody, $this->secret);
    }

    public function isValid(string $rawBody, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        return hash_equals($this->sign($rawBody), $signature);
    }
}
