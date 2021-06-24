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

       // if (is_float($value) || is_int($value)) {
            return (float) $value;
      //  }

      //  return 0;
    }
}
