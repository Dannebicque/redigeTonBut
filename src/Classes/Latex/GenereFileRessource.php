<?php


namespace App\Classes\Latex;


use App\Entity\ApcRessource;
use App\Latex\LatexSanitizer;
use Twig\Environment;

class GenereFileRessource
{

    public function __construct(
        protected Environment $twig,
        private readonly LatexSanitizer $latexSanitizer,
    ) {
    }

    public function genereFile(ApcRessource $ressource, string $chemin): string
    {
        $content = $this->twig->render('latex/exemple_ressource.tex.twig', [
            'ressource' => $ressource,
        ]);
        $content = $this->latexSanitizer->normalizeLatexDocument($content);
        $name = $chemin.'PN-BUT-' . $ressource->getVersion()?->getDepartement()->getSigle().'-'.$ressource->getSlugName().'.tex';
        $fichier = fopen($name, 'wb+');
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
