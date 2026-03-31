<?php


namespace App\Classes\Latex;


use App\Entity\ApcSae;
use Twig\Environment;

class GenereFileSae
{

    public function __construct(
        protected Environment $twig
    ) {
    }

    public function genereFile(ApcSae $sae, string $chemin): string
    {
        $content = $this->twig->render('latex/exemple_sae.tex.twig', [
            'sae' => $sae,
        ]);
        $name = $chemin.'/PN-BUT-' . $sae->getVersion()->getDepartement()->getSigle().'-'.$sae->getSlugName().'.tex';
        $fichier = fopen($name, 'wb+');
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
