<?php


namespace App\Utils;


class Convert
{
    public static function convertToFloat(mixed $value): ?float
    {
        $value = trim($value);
        str_replace([',', '.'], '.', $value);
        if ('' === $value || null === $value) {
            return 0;
        }

        return (float)$value;
    }
}
