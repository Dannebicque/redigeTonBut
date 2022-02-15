<?php

namespace App\Twig;

use Parsedown;
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
            new TwigFilter('markdown_to_latex', [$this, 'markdownToLatex'], ['is_safe' => ['html']]),
        ];
    }

    public function markdownToLatex($text)
    {
        $text = nl2br($text);
        $text = str_replace(['•	','<br/>', '<br />','<br>'], ['* ',"\r\n\r\n","\r\n\r\n","\r\n\r\n"], $text);
        $parse = new Parsedown();
        $text = $parse->text($text);
        $text = str_replace(
            ['<p>','</p>','<ul>','</ul>','<li>','</li>','<ol>','</ol>','<strong>','</strong>','&', 'œ', '’','«','»'],
            ['',"\r\n\r\n",'\begin{itemize}'."\r\n", '\end{itemize}'."\r\n", '\item ','','\begin{enumerate}'."\r\n",'\end{enumerate}'."\r\n",'\textbf{','}'."\r\n",'\&', '\oe{}', '\'','\og','\fg'], $text);

        return $text;
    }

    public function latexPurge($text)
    {
//        $text = str_replace(
//            ['&', 'œ', '’','•','â','à','é','è','ê','ë','î','ï','ô','ù','û','ü','ç'],
//            ['\&', '\oe{}', '\'','*','\\^a','\\`a','\\\'e','\\`e','\\^e','\\"e','\\^i','\\"i','\\^o','\\`u','\\^u','\\"u','\\c{c}'], $text);
       return str_replace(
            ['&', 'œ', '’','•','–'],
            ['\&', '\oe{}', '\'','*','--'], $text);
    }

    public function keyWords($text)
    {
        $text = str_replace(['-','–'], [' -- ',' -- '], $text);
        $text = str_replace(['&', 'œ', '’','•',', ', ';', ','], ['\&', '\oe{}', '\'','*',' -- ',' -- ',' -- '], $text);
        return $text;
    }
}
