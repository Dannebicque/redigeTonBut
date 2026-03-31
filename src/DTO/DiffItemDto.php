<?php

namespace App\DTO;

readonly class DiffItemDto
{
    public function __construct(
        public string $path,
        public mixed  $oldValue,
        public mixed  $newValue,
        public string $type // 'ajout', 'suppression', 'modification', 'ordre'
    )
    {
    }

    public function isModification(): bool
    {
        return $this->type === 'modification';
    }

    public function isAddition(): bool
    {
        return $this->type === 'ajout';
    }

    public function isDeletion(): bool
    {
        return $this->type === 'suppression';
    }

    public function isOrderChange(): bool
    {
        return $this->type === 'ordre';
    }
}
