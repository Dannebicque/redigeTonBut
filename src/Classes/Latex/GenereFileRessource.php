<?php


namespace App\Classes\Latex;


use App\Classes\Apc\ApcStructure;
use App\Entity\ApcRessource;
use App\Entity\Departement;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use DateTime;
use Twig\Environment;

class GenereFileRessource
{

    public function __construct(
        protected Environment $twig
    ) {
    }

    public function genereFile(ApcRessource $ressource, string $chemin)
    {
        $content = $this->twig->render('latex/exemple_ressource.tex.twig', [
            'ressource' => $ressource,
        ]);
        $name = $chemin.'/PN-BUT-' . $ressource->getDepartement()->getSigle().'-'.$ressource->getSlugName().'.tex';
        $fichier = fopen($name, 'wb+');
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
