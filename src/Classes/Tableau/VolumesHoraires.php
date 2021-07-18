<?php


namespace App\Classes\Tableau;

use App\DTO\VolumesHorairesSemestre;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;

class VolumesHoraires
{
    private array $semestres;
    private ?ApcParcours $parcours = null;

    private array $donneesSemestres;

    private ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    private ApcRessourceRepository $apcRessourceRepository;

    public function __construct(
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository
    ) {
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
    }

    public function setSemestres(array $semestres, ApcParcours $parcours = null)
    {
        $this->semestres = $semestres;
        $this->parcours = $parcours;

        return $this;
    }

    public function getDataJson()
    {
        $this->donneesSemestres = [];
        $json = [];
        /** @var \App\Entity\Semestre $semestre */
        foreach ($this->semestres as $semestre)
        {
            if ($this->parcours === null) {
                $ressources = $this->apcRessourceRepository->findBySemestre($semestre);
            } else {
                $ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $this->parcours);
            }


            $sem = new VolumesHorairesSemestre($semestre, $ressources);
            $json[$semestre->getOrdreLmd()] = $sem->getJson();
        }

        return $json;
    }

    public function semestre(int $i): ?VolumesHorairesSemestre
    {
        if (array_key_exists($i, $this->donneesSemestres)) {
            return $this->donneesSemestres[$i];
        }
        return null;
    }
}
