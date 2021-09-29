<?php

namespace App\Classes\Apc;

use App\Classes\Tableau\Preconisation;
use App\Entity\ApcParcours;
use App\Entity\Semestre;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcParcoursNiveauRepository;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;

class TableauPreconisation
{
    protected ApcSaeRepository $apcSaeRepository;
    protected ApcParcoursNiveauRepository $apcParcoursNiveauRepository;
    protected ApcRessourceRepository $apcRessourceRepository;
    protected ApcSaeParcoursRepository $apcSaeParcoursRepository;
    protected ApcRessourceParcoursRepository $apcRessourceParcoursRepository;
    protected ApcNiveauRepository $apcNiveauRepository;
    protected Preconisation $preconisation;

    private mixed $saes;
    private mixed $ressources;
    private mixed $niveaux;

    public function getSaes(): mixed
    {
        return $this->saes;
    }

    public function getRessources(): mixed
    {
        return $this->ressources;
    }

    public function getNiveaux(): mixed
    {
        return $this->niveaux;
    }

    public function __construct(
        ApcSaeRepository $apcSaeRepository,
        ApcParcoursNiveauRepository $apcParcoursNiveauRepository,
        ApcRessourceRepository $apcRessourceRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcNiveauRepository $apcNiveauRepository,
        Preconisation $preconisation,
    ) {
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcParcoursNiveauRepository = $apcParcoursNiveauRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
        $this->apcSaeParcoursRepository = $apcSaeParcoursRepository;
        $this->apcRessourceParcoursRepository = $apcRessourceParcoursRepository;
        $this->apcNiveauRepository = $apcNiveauRepository;
        $this->preconisation = $preconisation;
    }

    public function getDatas(Semestre $semestre, ?ApcParcours $parcours)
    {
        if ($parcours === null) {
            $this->saes = $this->apcSaeRepository->findBySemestre($semestre);
            $this->ressources = $this->apcRessourceRepository->findBySemestre($semestre);
            $this->niveaux = $this->apcNiveauRepository->findBySemestre($semestre);
        } else {
            $this->saes = $this->apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
            $this->ressources = $this->apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
            $this->niveaux = $this->apcParcoursNiveauRepository->findBySemestre($semestre, $parcours);
        }
    }

    public function getPreconisation($semestres, $parcours)
    {
        return $this->preconisation->setSemestresCompetences($semestres, $parcours)->getDataJson();
    }


}
