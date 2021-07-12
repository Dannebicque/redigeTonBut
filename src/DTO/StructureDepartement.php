<?php


namespace App\DTO;


use App\Entity\Departement;

class StructureDepartement
{
    private Departement $departement;

    public float $nbHeuresRessourcesSae = 0;
    public float $pourcentageAdaptationLocale = 0;
    public float $nbHeuresEnseignementLocale = 0;

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

    public function setDepartement(Departement $departement): void
    {
        $this->departement = $departement;
    }

    public function addSemestre(StructureSemestre $semestre): void
    {
        $this->nbHeuresRessourcesSae += $semestre->nbHeuresRessourcesSae;
        $this->totalPourcentageAdaptationLocale += $semestre->pourcentageAdaptationLocale;
        $this->nbHeuresEnseignementLocale += $semestre->nbHeuresEnseignementLocale;

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

    public function getMoyenneAdaptationLocale(): float
    {
        return $this->totalPourcentageAdaptationLocale / 6;
    }

    public function getJson(): array
    {
        return [
            'nbHeuresRessourcesSae' => $this->nbHeuresRessourcesSae,
            'totalPourcentageAdaptationLocale' => $this->getMoyenneAdaptationLocale(),
            'nbHeuresEnseignementLocale' => $this->nbHeuresEnseignementLocale,

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
            'ecart' => $this->departement->getNbHeuresDiplome() - $this->nbHeuresRessourcesSae,
            'ecartProjet' => Caracteristique::NB_HEURES_PROJET - $this->nbHeuresProjet,
        ];
    }
}
