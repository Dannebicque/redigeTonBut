<?php


namespace App\DTO;


use App\Entity\Semestre;

class StructureSemestre
{
    public float $nbHeuresRessourcesSae;
    public float $pourcentageAdaptationLocale;
    public float $nbHeuresAdaptationLocale;
//    public float $nbHeuresSae;
//    public float $nbHeuresRessources;
    public int $nbSemaines;
    public int $nbSemainesConges;
    public int $nbSemainesStageMin;
    public int $nbSemainesStageMax;
    public int $nbSemainesCoursProjet;
    public float $nbHeuresProjet;
    public float $nbHeuresCoursProjet;
    public int $nbDemiJournees;
    public float $dureeHebdo;
    public float $nbMoyenneHeuresDemiJournee;
    public float $nbHeuresCoursHebdo;
    public float $nbHeuresHebdoProjet;

    public float $nbHeuresEnseignementLocale;
    public float $nbHeuresEnseignementSaeLocale;
    public float $nbHeuresEnseignementRessourceLocale;
    public float $nbHeuresEnseignementRessourceNational; // a calculer


    public function __construct(Semestre $semestre)
    {
        $this->nbHeuresRessourcesSae = $semestre->getNbHeuresRessourceSae();
        $this->pourcentageAdaptationLocale = $semestre->getPourcentageAdaptationLocale();
        $this->nbHeuresAdaptationLocale = $semestre->getNbHeuresRessourceSae() * $semestre->getPourcentageAdaptationLocale() / 100;
//        $this->nbHeuresSae = $semestre->getNbHeuresSae();
//        $this->nbHeuresRessources = $semestre->getNbHeuresRessources();

        $this->nbHeuresEnseignementLocale = $semestre->getNbHeuresEnseignementLocale();
        $this->nbHeuresEnseignementSaeLocale = $semestre->getNbHeuresEnseignementSaeLocale();
        $this->nbHeuresEnseignementRessourceLocale = $semestre->getNbHeuresEnseignementRessourceLocale();

        $this->nbSemaines = 17;
        $this->nbSemainesConges = 3;
        $this->nbSemainesStageMin = $semestre->getNbSemaineStageMin();
        $this->nbSemainesStageMax = $semestre->getNbSemainesStageMax();
        $this->nbSemainesCoursProjet = $this->nbSemaines - $this->nbSemainesConges;
        $this->nbHeuresProjet = $semestre->getNbHeuresProjet();
        $this->nbHeuresCoursProjet = $this->nbHeuresRessourcesSae + $this->nbHeuresProjet;
        $this->nbDemiJournees = 9;
        $this->dureeHebdo = $this->nbHeuresCoursProjet / $this->nbSemainesCoursProjet;
        $this->nbMoyenneHeuresDemiJournee = $this->dureeHebdo / $this->nbDemiJournees;
        $this->nbHeuresCoursHebdo = $this->nbHeuresRessourcesSae / $this->nbSemainesCoursProjet;
        $this->nbHeuresHebdoProjet = $this->nbHeuresProjet / $this->nbSemainesCoursProjet;

    }

    public function getJson()
    {
        return [
            'nbHeuresRessourcesSae' => $this->nbHeuresRessourcesSae,
            'pourcentageAdaptationLocale' => $this->pourcentageAdaptationLocale,
            'nbHeuresAdaptationLocale' => $this->nbHeuresAdaptationLocale,
            'nbHeuresEnseignementLocale' => $this->nbHeuresEnseignementLocale,
            'nbHeuresEnseignementSaeLocale' => $this->nbHeuresEnseignementSaeLocale,
            'nbHeuresEnseignementRessourceLocale' => $this->nbHeuresEnseignementRessourceLocale,
            'nbSemaines' => $this->nbSemaines,
            'nbSemainesConges' => $this->nbSemainesConges,
            'nbSemainesStageMin' => $this->nbSemainesStageMin,
            'nbSemainesStageMax' => $this->nbSemainesStageMax,
            'nbSemainesCoursProjet' => $this->nbSemainesCoursProjet,
            'nbHeuresProjet' => $this->nbHeuresProjet,
            'nbHeuresCoursProjet' => $this->nbHeuresCoursProjet,
            'nbDemiJournees' => $this->nbDemiJournees,
            'dureeHebdo' => $this->dureeHebdo,
            'nbMoyenneHeuresDemiJournee' => $this->nbMoyenneHeuresDemiJournee,
            'nbHeuresCoursHebdo' => $this->nbHeuresCoursHebdo,
            'nbHeuresHebdoProjet' => $this->nbHeuresHebdoProjet
        ];
    }

}
