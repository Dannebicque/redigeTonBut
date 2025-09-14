<?php

namespace App\Classes\Export;

class DiffObject
{

    /**
     * @param int|string $key
     * @param mixed $itemActuel
     * @param mixed|null $param
     */
    public function __construct(
        public string $key,
        public string|array|null $itemActuel,
        public string|array|null $itemAncien = null)
    {
    }
}
