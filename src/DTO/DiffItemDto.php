<?php

namespace App\DTO;

class DiffItemDto
{
    public function __construct(
        public readonly string $path,
        public readonly mixed $oldValue,
        public readonly mixed $newValue,
        public readonly string $type // 'ajout', 'suppression', 'modification', 'ordre'
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
