<?php


namespace App\Classes\Latex;


use App\Entity\ApcSae;
use App\Latex\LatexSanitizer;
use Twig\Environment;

class GenereFileSae
{

    public function __construct(
        protected Environment $twig,
        private readonly LatexSanitizer $latexSanitizer,
    ) {
    }

    public function genereFile(ApcSae $sae, string $chemin): string
    {
        $content = $this->twig->render('latex/exemple_sae.tex.twig', [
            'sae' => $sae,
        ]);
        $content = $this->latexSanitizer->normalizeLatexDocument($content);
        $name = $chemin.'/PN-BUT-' . $sae->getVersion()->getDepartement()->getSigle().'-'.$sae->getSlugName().'.tex';
        $fichier = fopen($name, 'wb+');
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
