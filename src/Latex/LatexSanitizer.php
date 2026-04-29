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
            ['\\', '&', '%', '$', '#', '_', '{', '}', '~', '^', 'œ', 'Œ', 'æ', 'Æ', '°', '...'],
            ['\textbackslash{}', '\&', '\%', '\$', '\#', '\_', '\{', '\}', '\textasciitilde{}', '\textasciicircum{}', '\oe{}', '\OE{}', 'ae', 'AE', '$ ^\circ$ ', '\ldots '],
            $text,
        );
    }

    /**
     * Normalise le texte UTF-8 en conservant uniquement des caractères raisonnablement sûrs pour pdflatex.
     */
    public function normalizeUnicode(?string $text): string
    {
        $text = $this->ensureUtf8($text);

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

    /**
     * Convertit un document LaTeX complet en source sûre pour des outils qui relisent les fichiers/logs en UTF-8 strict.
     * Les accents sont conservés dans le PDF via des macros LaTeX ASCII.
     */
    public function normalizeLatexDocument(?string $text): string
    {
        $text = $this->normalizeUnicode($text);

        if ($text === '') {
            return '';
        }

        $text = strtr($text, $this->getLatexAccentMap());

        return $this->replaceRemainingNonAsciiForLatexDocument($text);
    }

    private function ensureUtf8(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (preg_match('//u', $text) === 1) {
            return $text;
        }

        $repaired = $this->repairMixedUtf8AndSingleByteEncoding($text);
        if (preg_match('//u', $repaired) === 1) {
            return $repaired;
        }

        $converted = @iconv('CP1252', 'UTF-8//IGNORE', $text);
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }

        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $text);

        return is_string($converted) ? $converted : '';
    }

    private function repairMixedUtf8AndSingleByteEncoding(string $text): string
    {
        $length = strlen($text);
        $output = '';
        $index = 0;

        while ($index < $length) {
            $byte = ord($text[$index]);

            if ($byte <= 0x7F) {
                $output .= $text[$index];
                ++$index;
                continue;
            }

            $sequenceLength = $this->getUtf8SequenceLength($text, $index, $length);
            if ($sequenceLength > 0) {
                $output .= substr($text, $index, $sequenceLength);
                $index += $sequenceLength;
                continue;
            }

            $output .= $this->convertSingleByteToUtf8($text[$index]);
            ++$index;
        }

        return $output;
    }

    private function getUtf8SequenceLength(string $text, int $index, int $length): int
    {
        $first = ord($text[$index]);

        if ($first >= 0xC2 && $first <= 0xDF && $index + 1 < $length) {
            $b1 = ord($text[$index + 1]);
            return $this->isContinuationByte($b1) ? 2 : 0;
        }

        if ($first >= 0xE0 && $first <= 0xEF && $index + 2 < $length) {
            $b1 = ord($text[$index + 1]);
            $b2 = ord($text[$index + 2]);
            return ($this->isContinuationByte($b1) && $this->isContinuationByte($b2)) ? 3 : 0;
        }

        if ($first >= 0xF0 && $first <= 0xF4 && $index + 3 < $length) {
            $b1 = ord($text[$index + 1]);
            $b2 = ord($text[$index + 2]);
            $b3 = ord($text[$index + 3]);
            return ($this->isContinuationByte($b1) && $this->isContinuationByte($b2) && $this->isContinuationByte($b3)) ? 4 : 0;
        }

        return 0;
    }

    private function isContinuationByte(int $byte): bool
    {
        return $byte >= 0x80 && $byte <= 0xBF;
    }

    private function convertSingleByteToUtf8(string $byte): string
    {
        $converted = @iconv('CP1252', 'UTF-8//IGNORE', $byte);
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }

        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $byte);

        return is_string($converted) ? $converted : '';
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

    /**
     * @return array<string, string>
     */
    private function getLatexAccentMap(): array
    {
        return [
            'À' => "\\`{A}", 'Á' => "\\'{A}", 'Â' => "\\^{A}", 'Ã' => "\\~{A}", 'Ä' => "\\\"{A}", 'Å' => "\\r{A}",
            'à' => "\\`{a}", 'á' => "\\'{a}", 'â' => "\\^{a}", 'ã' => "\\~{a}", 'ä' => "\\\"{a}", 'å' => "\\r{a}",
            'Æ' => "\\AE{}", 'æ' => "\\ae{}",
            'Ç' => "\\c{C}", 'ç' => "\\c{c}",
            'È' => "\\`{E}", 'É' => "\\'{E}", 'Ê' => "\\^{E}", 'Ë' => "\\\"{E}",
            'è' => "\\`{e}", 'é' => "\\'{e}", 'ê' => "\\^{e}", 'ë' => "\\\"{e}",
            'Ì' => "\\`{I}", 'Í' => "\\'{I}", 'Î' => "\\^{I}", 'Ï' => "\\\"{I}",
            'ì' => "\\`{i}", 'í' => "\\'{i}", 'î' => "\\^{i}", 'ï' => "\\\"{i}",
            'Ñ' => "\\~{N}", 'ñ' => "\\~{n}",
            'Ò' => "\\`{O}", 'Ó' => "\\'{O}", 'Ô' => "\\^{O}", 'Õ' => "\\~{O}", 'Ö' => "\\\"{O}", 'Ø' => "\\O{}",
            'ò' => "\\`{o}", 'ó' => "\\'{o}", 'ô' => "\\^{o}", 'õ' => "\\~{o}", 'ö' => "\\\"{o}", 'ø' => "\\o{}",
            'Œ' => "\\OE{}", 'œ' => "\\oe{}",
            'Ù' => "\\`{U}", 'Ú' => "\\'{U}", 'Û' => "\\^{U}", 'Ü' => "\\\"{U}",
            'ù' => "\\`{u}", 'ú' => "\\'{u}", 'û' => "\\^{u}", 'ü' => "\\\"{u}",
            'Ý' => "\\'{Y}", 'Ÿ' => "\\\"{Y}",
            'ý' => "\\'{y}", 'ÿ' => "\\\"{y}",
            'Š' => "\\v{S}", 'š' => "\\v{s}", 'Ž' => "\\v{Z}", 'ž' => "\\v{z}",
            'Ð' => 'D', 'ð' => 'd', 'Þ' => 'Th', 'þ' => 'th', 'ß' => 'ss',
            '¡' => '!`', '¿' => '?`',
        ];
    }

    private function replaceRemainingNonAsciiForLatexDocument(string $text): string
    {
        $result = preg_match_all('/[^\x09\x0A\x0D\x20-\x7E]/u', $text, $matches);
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

