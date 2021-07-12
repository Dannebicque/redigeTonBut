<?php


namespace App\DTO;


class StructureDepartement
{
    public float $nbHeuresRessourcesSae = 0;
    public float $pourcentageAdaptationLocale = 0;
    public float $nbHeuresAdaptationLocale = 0;
//    public float $nbHeuresSae = 0;
//    public float $nbHeuresRessources = 0;
    public int $nbSemaines = 0;
    public int $nbSemainesConges = 0;
    public int $nbSemainesStageMin = 0;
    public int $nbSemainesStageMax = 0;
    public int $nbSemainesCoursProjet = 0;
    public float $nbHeuresProjet = 0;
    public float $nbHeuresCoursProjet = 0;
    public int $nbDemiJournees = 0;
    public float $dureeHebdo = 0;
    public float $nbMoyenneHeuresDemiJournee = 0;
    public float $nbHeuresCoursHebdo = 0;
    public float $nbHeuresHebdoProjet = 0;
    private float $totalPourcentageAdaptationLocale = 0;

    public function addSemestre(StructureSemestre $semestre)
    {
        $this->nbHeuresRessourcesSae += $semestre->nbHeuresRessourcesSae;
        $this->totalPourcentageAdaptationLocale += $semestre->pourcentageAdaptationLocale;
        $this->nbHeuresAdaptationLocale += $semestre->nbHeuresAdaptationLocale;
//        $this->nbHeuresSae += $semestre->nbHeuresSae;
//        $this->nbHeuresRessources += $semestre->nbHeuresRessources;
        $this->nbSemaines += $semestre->nbSemaines;
        $this->nbSemainesConges += $semestre->nbSemainesConges;
        $this->nbSemainesStageMin += $semestre->nbSemainesStageMin;
        $this->nbSemainesStageMax += $semestre->nbSemainesStageMax;
        $this->nbSemainesCoursProjet += $this->nbSemainesCoursProjet;
        $this->nbHeuresProjet += $semestre->nbHeuresProjet;
        $this->nbHeuresCoursProjet += $semestre->nbHeuresCoursProjet;
        $this->nbDemiJournees += 9;
        $this->dureeHebdo += $semestre->dureeHebdo;
        $this->nbMoyenneHeuresDemiJournee += $semestre->nbMoyenneHeuresDemiJournee;
        $this->nbHeuresCoursHebdo += $semestre->nbHeuresCoursHebdo;
        $this->nbHeuresHebdoProjet += $semestre->nbHeuresHebdoProjet;
    }

    public function getMoyenneAdaptationLocale()
    {
        return $this->totalPourcentageAdaptationLocale / 6;
    }

    public function getJson()
    {
        return [
            'nbHeuresRessourcesSae' => $this->nbHeuresRessourcesSae,
            'totalPourcentageAdaptationLocale' => $this->totalPourcentageAdaptationLocale,
            'nbHeuresAdaptationLocale' => $this->nbHeuresAdaptationLocale,
//            'nbHeuresSae' => $this->nbHeuresSae,
//            'nbHeuresRessources' => $this->nbHeuresRessources,
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
            'nbHeuresHebdoProjet' => $this->nbHeuresHebdoProjet,
        ];
    }
}
