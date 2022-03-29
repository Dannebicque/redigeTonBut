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
        $text = str_replace(['SAé', ' BUT.', ' BUT ', '•	','<br/>', '<br />','<br>'], ['SAÉ', ' B.U.T.', ' B.U.T. ','* ',"\r\n\r\n","\r\n\r\n","\r\n\r\n"], $text);
        $parse = new Parsedown();
        $text = $parse->text($text);
        $text = str_replace(
            ['<em>','</em>','<p>','</p>','<ul>','</ul>','<li>','</li>','<ol>','</ol>','<strong>','</strong>','&', 'œ', '’','«','»', '°','\&lt;','%','→','…', '**Z**','\&quot;', '\&amp;','℃'],
            ['','','',"\r\n\r\n",'\begin{itemize}'."\r\n", '\end{itemize}'."\r\n", '\item[--] ','','\begin{enumerate}'."\r\n",'\end{enumerate}'."\r\n",'\textbf{','}','\&', '\oe{}', '\'','\og','\fg ', '$ ^\circ$ ', '> ','\%','->','...','\mathbb{Z}','"','\&','$ ^\circ$C'], $text);
        $text = str_replace('<ol start="', '\begin{enumerate}\setcounter{enumi}{', $text);
        $text = str_replace('">', '-1}', $text);

        return $text;
    }

    public function latexPurge($text)
    {
       return str_replace(
            ['SAé', ' BUT.', ' BUT ','&', 'œ', '’','•','–','\&quot;', '\&amp;'],
            ['SAÉ', ' B.U.T.', ' B.U.T. ','\&', '\oe{}', '\'','*','--','"','\&'], $text);
    }

    public function keyWords($text)
    {
        $text = str_replace([' - ',' – '], [' -- ',' -- '], $text);
        $text = str_replace(['&', 'œ', '’','•',', ', ';', ',','\&quot;', '\&amp;'], ['\&', '\oe{}', '\'','*',' -- ',' -- ',' -- ','"','\&'], $text);
        return $text;
    }
}
