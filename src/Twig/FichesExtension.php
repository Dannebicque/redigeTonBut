<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FichesExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('keyWords', [$this, 'keyWords'], ['is_safe' => ['html']]),
        ];
    }

    public function keyWords($text)
    {
        $text = str_replace([', ', ';', ','], [' - ',' - ',' - '], $text);
        return $text;
    }
}
