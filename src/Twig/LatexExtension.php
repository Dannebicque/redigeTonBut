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
//        $text = str_replace(
//            ['&', 'œ', '’','•','â','à','é','è','ê','ë','î','ï','ô','ù','û','ü','ç'],
//            ['\&', '\oe{}', '\'','*','\\^a','\\`a','\\\'e','\\`e','\\^e','\\"e','\\^i','\\"i','\\^o','\\`u','\\^u','\\"u','\\c{c}'], $text);
        $text = str_replace(
            ['&', 'œ', '’','•'],
            ['\&', '\oe{}', '\'','*'], $text);
        // Latex pour le "gras"
        $pat1 = '/(^\\*\\*)+/m';
        $pat2 = '/(\\*\\*\\s)+/m';
        $pat3 = '/(\\*\\*$)+/m';
        $text = preg_replace($pat1, "\\textbf{", $text);
        $text = preg_replace($pat2, "} ", $text);
        $text = preg_replace($pat3, "} ", $text);

        $tab = explode("\n", $text);
        $tab2 = [];
        $i = 0;
        $start = false;
        foreach ($tab as $key => $value) {
            $ligne = ltrim($value);
            if ($ligne !== '') {
                if ($ligne[0] === '*' || $ligne[0] === '•') {
                    if ($start === false) {
                        $start = true;
                        $tab2[$i] = '\begin{itemize}';
                        $i++;
                    }
                    $tab2[$i] = '\\item ' . substr($ligne, 1);
                    $i++;
                } else {
                    if ($start === true) {
                        $start = false;
                        $tab2[$i] = '\end{itemize}';
                        $i++;
                        $tab2[$i] = "\n\n";
                        $i++;
                    }
                    $tab2[$i] = $value;
                    $i++;
                }
            } else {
                if ($start === true) {
                    $start = false;
                    $tab2[$i] = '\end{itemize}';
                    $i++;
                    $tab2[$i] = "\n\n";
                    $i++;
                } else if ($i > 1 && $tab2[$i-1] !== "\n\n") {
                    $tab2[$i] = "\n\n";
                    $i++;
                }
            }
        }

        if ($start === true) {
            $tab2[$i] = '\end{itemize}';
            $i++;
            $tab2[$i] = "\n\n";

        }

        return implode("\n", $tab2);
        // Fin latex pour le "gras"
    }

    public function keyWords($text)
    {
        return str_replace([', ', ';', ','], [' - '], $text);
    }
}
