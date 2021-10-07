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
    private float $totalAdaptationLocale = 0;
    private float $totalTp = 0;

    public function setDepartement(Departement $departement): void
    {
        $this->departement = $departement;
    }

    public function addSemestre(StructureSemestre $semestre): void
    {
        $this->nbHeuresRessourcesSae += $semestre->nbHeuresRessourcesSae;
        $this->totalAdaptationLocale += $semestre->nbHeuresEnseignementLocale;
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
        $this->totalTp += $semestre->nbHeuresTpLocale + $semestre->nbHeuresTpNational;
    }

    public function getMoyenneAdaptationLocale(): float
    {
        return $this->nbHeuresEnseignementLocale / $this->departement->getNbHeuresDiplome() * 100;
    }

    public function getJson(): array
    {
        if ($this->departement->isTertiaire()) {
            $nbHeuresTp = Tertiaire::NB_HEURES_TP;
        } else {
            $nbHeuresTp = Secondaire::NB_HEURES_TP;
        }


        return [
            'nbHeuresRessourcesSae' => round($this->nbHeuresRessourcesSae, 2),
            'totalPourcentageAdaptationLocale' => round($this->getMoyenneAdaptationLocale(), 2),
            'nbHeuresEnseignementLocale' => round($this->nbHeuresEnseignementLocale, 2),
            'totalAdaptationLocale' => round($this->totalAdaptationLocale, 2),

            'nbSemaines' => $this->nbSemaines,
            'totalTp' => round($this->totalTp, 2),
            'ecartTotalTp' => round($nbHeuresTp - $this->totalTp, 2),
            'nbSemainesConges' => $this->nbSemainesConges,
            'nbSemainesStageMin' => $this->nbSemainesStageMin,
            'nbSemainesStageMax' => $this->nbSemainesStageMax,
            'nbSemainesCoursProjet' => $this->nbSemainesCoursProjet,
            'nbHeuresProjet' => round($this->nbHeuresProjet, 2),
            'nbHeuresCoursProjet' => round($this->nbHeuresCoursProjet, 2),
            'nbDemiJournees' => $this->nbDemiJournees,
            'dureeHebdo' => round($this->dureeHebdo, 2),
            'nbMoyenneHeuresDemiJournee' => round($this->nbMoyenneHeuresDemiJournee, 2),
            'nbHeuresCoursHebdo' => round($this->nbHeuresCoursHebdo, 2),
            'nbHeuresHebdoProjet' => round($this->nbHeuresHebdoProjet, 2),
            'ecart' => round($this->departement->getNbHeuresDiplome() - $this->nbHeuresRessourcesSae, 2),
            'ecartProjet' => round(Caracteristique::NB_HEURES_PROJET - $this->nbHeuresProjet, 2),
        ];
    }
}
