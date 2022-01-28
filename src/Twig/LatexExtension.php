<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LatexExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('latex_purge', [$this, 'latexPurge'], ['is_safe' => ['html']]),
            new TwigFilter('keyWordsLatex', [$this, 'keyWords'], ['is_safe' => ['html']]),
        ];
    }

    public function latexPurge($text)
    {
        $text = str_replace(['&'], ['\&'], $text);
        // Latex pour le "gras"
        $pat1 = '/(^\\*\\*)+/m';
        $pat2 = '/(\\*\\*\\s)+/m';
        $pat3 = '/(\\*\\*$)+/m';
        $text = preg_replace($pat1, "\\textbf{", $text);
        $text = preg_replace($pat2, "} ", $text);
        $text = preg_replace($pat3, "} ", $text);
        // Fin latex pour le "gras"
        return $text;
    }

    public function keyWords($text)
    {
        return str_replace([',', ';'], ['-'], $text);
    }
}
