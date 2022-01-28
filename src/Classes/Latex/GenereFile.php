<?php


namespace App\Classes\Latex;


use App\Classes\Apc\ApcStructure;
use App\Entity\Departement;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use DateTime;
use Twig\Environment;

class GenereFile
{
    protected Departement $departement;
    protected Environment $twig;
    protected ApcStructure $apcStructure;
    protected string $chemin;


    public function __construct(
        ApcRessourceRepository $apcRessourceRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcStructure $apcStructure,
        Environment $twig,
    ) {
        $this->apcStructure = $apcStructure;
        $this->twig = $twig;
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
    }

    public function genereFile(Departement $departement, string $chemin)
    {
        $parcours = $departement->getApcParcours();
        $tParcours = $this->apcStructure->parcoursNiveaux($departement);
        $competences = $departement->getApcCompetences();

        $tComp = [];
        foreach ($competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }
        $competencesParcours = [];


        foreach ($tParcours as $key => $parc) {
            $competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $competencesParcours[$key][] = $tComp[$k];
            }
        }

        $semestres = $departement->getSemestres();
        foreach ($semestres as $semestre) {
            if ($departement->getTypeStructure() === Departement::TYPE3 || $semestre->getOrdreLmd() > 2) {
                foreach ($parcours as $parcour) {
                    $ressources[$semestre->getId()][$parcour->getId()] = $this->apcRessourceParcoursRepository->findBySemestreArray($semestre,
                        $parcour);
                    $saes[$semestre->getId()][$parcour->getId()] = $this->apcSaeParcoursRepository->findBySemestreArray($semestre,
                        $parcour);
                }
            } else {
                $ressources[$semestre->getId()] = $this->apcRessourceRepository->findBySemestreArray($semestre);
                $saes[$semestre->getId()] = $this->apcSaeRepository->findBySemestreArray($semestre);
            }
        }

        $content = $this->twig->render('latex/annexe_specialite.tex.twig', [
            'departement' => $departement,
            'competencesParcours' => $competencesParcours,
            'saes' => $saes,
            'ressources' => $ressources,
        ]);
        $cle = new DateTime('now');
        $name = $chemin . 'PN-BUT-' . $departement->getSigle() . '.tex'; // . '-' . $cle->format('dmY-Hi') . '.tex';//todo: remettre en prod
        $fichier = fopen($name, 'wb+');
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
