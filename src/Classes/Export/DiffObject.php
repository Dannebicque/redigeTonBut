<?php

namespace App\Classes\Export;

class DiffObject
{

    /**
     * @param string $key
     * @param mixed $itemActuel
     * @param string|array|null $itemAncien
     */
    public function __construct(
        public string $key,
        public string|array|null $itemActuel,
        public string|array|null $itemAncien = null)
    {
    }
}
