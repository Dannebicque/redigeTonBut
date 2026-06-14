<?php

namespace App\Twig;

use App\Latex\LatexSanitizer;
use Parsedown;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LatexExtension extends AbstractExtension
{
    public function __construct(
        private readonly LatexSanitizer $latexSanitizer,
    ) {
    }

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

    public function markdownToLatex($text): array|string
    {
        $text = $this->latexSanitizer->sanitizeForLatex((string) $text);
        $text = nl2br($text);
        $text = str_replace(['SAé', ' BUT.', ' BUT ', '•	','<br/>', '<br />','<br>'], ['SAÉ', ' B.U.T.', ' B.U.T. ','* ',"\r\n\r\n","\r\n\r\n","\r\n\r\n"], $text);

        $parse = new Parsedown();
        $text = $parse->text($text);
        $text = str_replace(
            ['<em>','</em>','<p>','</p>','<ul>','</ul>','<li>','</li>','<ol>','</ol>','<strong>','</strong>', '#', 'œ', '’','«','»', '°','\&lt;','%','→','…', 'ℤ','\&quot;', '\&amp;', '&amp;','℃', '<ol start="'],
            ['','','',"\r\n\r\n",'\begin{itemize}'."\r\n", '\end{itemize}'."\r\n", '\item[--] ','','\begin{enumerate}'."\r\n",'\end{enumerate}'."\r\n",'\textbf{','}','\#', '\oe{}', "'",'\og ',' r\fg ', '$ ^\circ$ ', '> ','\%','->','...','$\mathbb{Z}$','"','\&','\&','$ ^\circ$C', '\begin{enumerate}\setcounter{enumi}{'], $text);

        return $this->latexSanitizer->normalizeUnicode(str_replace('">', '-1}', $text));
    }

    public function latexPurge($text): array|string
    {
        return $this->latexSanitizer->sanitizeForLatex((string) $text);
    }

    public function keyWords($text): array|string
    {
        $text = $this->latexSanitizer->sanitizeForLatex((string) $text);
        $text = str_replace([' - ', ' – ', ', ', ';', ','], [' -- ', ' -- ', ' -- ', ' -- ', ' -- '], $text);

        return $this->latexSanitizer->normalizeUnicode($text);
    }
}
