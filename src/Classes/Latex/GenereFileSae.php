<?php


namespace App\Classes\Latex;


use App\Classes\Apc\ApcStructure;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\Departement;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use DateTime;
use Twig\Environment;

class GenereFileSae
{

    public function __construct(
        protected Environment $twig
    ) {
    }

    public function genereFile(ApcSae $sae, string $chemin)
    {
        $content = $this->twig->render('latex/exemple_sae.tex.twig', [
            'sae' => $sae,
        ]);
        $name = $chemin.'/PN-BUT-' . $sae->getDepartement()->getSigle().'-'.$sae->getSlugName().'.tex';
        $fichier = fopen($name, 'wb+');
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
