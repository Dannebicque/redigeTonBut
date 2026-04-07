<?php

declare(strict_types=1);

namespace App\Latex;

final class LatexSanitizer
{
    /**
     * Nettoie les caractères Unicode problématiques pour du texte brut destiné à LaTeX.
     */
    public function sanitizeForLatex(?string $text): string
    {
        $text = $this->normalizeUnicode($text);

        if ($text === '') {
            return '';
        }

        return str_replace(
            ['\\', '&', '%', '$', '#', '_', '{', '}', '~', '^', 'œ', 'Œ', 'æ', 'Æ', '°'],
            ['\textbackslash{}', '\\&', '\\%', '\\$', '\\#', '\\_', '\\{', '\\}', '\\textasciitilde{}', '\\textasciicircum{}', '\\oe{}', '\\OE{}', 'ae', 'AE', '$ ^\\circ$ '],
            $text,
        );
    }

    /**
     * Normalise le texte UTF-8 en conservant uniquement des caractères raisonnablement sûrs pour pdflatex.
     */
    public function normalizeUnicode(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        if ($text === '') {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if (is_string($normalized)) {
                $text = $normalized;
            }
        }

        $text = str_replace(
            [
                "\u{00A0}", "\u{1680}", "\u{2000}", "\u{2001}", "\u{2002}", "\u{2003}", "\u{2004}", "\u{2005}", "\u{2006}", "\u{2007}", "\u{2008}", "\u{2009}", "\u{200A}", "\u{202F}", "\u{205F}", "\u{3000}",
                "\u{200B}", "\u{200C}", "\u{200D}", "\u{2060}", "\u{FEFF}",
                "\u{00AD}",
                "\u{2028}", "\u{2029}",
                '’', '‘', '‛', 'ʼ',
                '“', '”', '„', '‟',
                '–', '—', '―', '‑',
                '•', '◦', '▪', '●', '∙',
                '…',
                '→', '←', '↔', '⇒', '⇐', '≤', '≥', '≠',
                '×', '÷', '−',
                '℃', '€', '™', '®', '©',
                'SAé', ' BUT.', ' BUT ',
            ],
            [
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                '', '', '', '', '',
                '',
                "\n", "\n",
                "'", "'", "'", "'",
                '"', '"', '"', '"',
                '--', '--', '--', '-',
                '* ', '* ', '* ', '* ', '* ',
                '...',
                '->', '<-', '<->', '=>', '<=', '<=', '>=', '!=',
                'x', '/', '-',
                ' deg C', 'EUR', '(TM)', '(R)', '(c)',
                'SAÉ', ' B.U.T.', ' B.U.T. ',
            ],
            $text,
        );

        $withoutControls = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        if (is_string($withoutControls)) {
            $text = $withoutControls;
        }

        return $this->replaceUnsupportedUnicodeCharacters($text);
    }

    private function replaceUnsupportedUnicodeCharacters(string $text): string
    {
        $result = preg_match_all('/[^\x09\x0A\x0D\x20-\x7E\x{00A1}-\x{024F}]/u', $text, $matches);
        if ($result !== 1 || !isset($matches[0]) || !is_array($matches[0])) {
            return $text;
        }

        $search = [];
        $replace = [];

        foreach (array_unique($matches[0]) as $character) {
            $search[] = $character;
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $character);
            $replace[] = is_string($converted) ? $converted : '';
        }

        return str_replace($search, $replace, $text);
    }
}

