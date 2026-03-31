<?php


namespace App\Classes\Latex;


use App\Classes\Apc\ApcStructure;
use App\Entity\Departement;
use App\Entity\Version;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use Twig\Environment;

class GenereFile
{
    /**
     * @var ApcRessourceRepository
     */
    public ApcRessourceRepository $apcRessourceRepository;
    /**
     * @var ApcRessourceParcoursRepository
     */
    public ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    /**
     * @var ApcSaeRepository
     */
    public ApcSaeRepository $apcSaeRepository;
    /**
     * @var ApcSaeParcoursRepository
     */
    public ApcSaeParcoursRepository $apcSaeParcoursRepository;
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

    public function genereFile(Version $version, string $chemin): string
    {
        $parcours = $version->getApcParcours();
        $tParcours = $this->apcStructure->parcoursNiveaux($version);
        $competences = $version->getApcCompetences();

        $tComp = [];
        foreach ($competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }

        $competencesParcours = [];
        $tSemestres = [];

        foreach ($tParcours as $key => $parc) {
            $tSemestres[$key] = [];
            $competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $competencesParcours[$key][] = $tComp[$k];
            }
        }

        $semestres = $version->getSemestres();

        $departement = $version->getDepartement();
        if ($departement->getTypeStructure() === Departement::TYPE3) {
            foreach ($semestres as $semestre) {
                $tSemestres[$semestre->getApcParcours()->getId()][$semestre->getOrdreLmd()] = [];
                $tSemestres[$semestre->getApcParcours()->getId()][$semestre->getOrdreLmd()]['semestre'] = $semestre;
                $tSemestres[$semestre->getApcParcours()->getId()][$semestre->getOrdreLmd()]['saes'] = $this->apcSaeParcoursRepository->findBySemestre($semestre, $semestre->getApcParcours());
                $tSemestres[$semestre->getApcParcours()->getId()][$semestre->getOrdreLmd()]['ressources'] = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $semestre->getApcParcours());
            }

            $content = $this->twig->render('latex/annexe_specialite.tex.twig', [
                'departement' => $departement,
                'semestres' => $tSemestres,
                'competencesParcours' => $competencesParcours
            ]);
        } else {
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
                'version' => $version,
                'competencesParcours' => $competencesParcours,
                'saes' => $saes,
                'ressources' => $ressources,
            ]);
        }


        $name = $chemin . 'PN-BUT-' . $departement->getSigle() . '.tex';
        $fichier = fopen($name, 'wb+');
        $content="\xEF\xBB\xBF".$content;
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
