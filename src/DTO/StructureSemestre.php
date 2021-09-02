<?php


namespace App\DTO;


use App\Entity\Semestre;

class StructureSemestre
{
    public float $nbHeuresRessourcesSae;
    public float $pourcentageAdaptationLocale;

    public float $nbSemaines;
    public float $nbSemainesConges;
    public int $nbSemainesStageMin;
    public int $nbSemainesStageMax;
    public int $nbSemainesCoursProjet;
    public float $nbHeuresProjet;
    public float $nbHeuresCoursProjet;
    public float $nbDemiJournees;
    public float $dureeHebdo;
    public float $nbMoyenneHeuresDemiJournee;
    public float $nbHeuresCoursHebdo;
    public float $nbHeuresHebdoProjet;

    public float $nbHeuresEnseignementLocale;
    public float $nbHeuresEnseignementSaeLocale;
    public float $nbHeuresEnseignementRessourceLocale;
    public float $nbHeuresEnseignementRessourceNational;
    public float $nbHeuresTpNational;
    public float $nbHeuresTpLocale;


    public function __construct(Semestre $semestre)
    {
        $this->nbHeuresRessourcesSae = $semestre->getNbHeuresRessourceSae();
        $this->pourcentageAdaptationLocale = $semestre->getPourcentageAdaptationLocale();

        $this->nbHeuresEnseignementLocale = $semestre->getNbHeuresEnseignementLocale();
        $this->nbHeuresEnseignementSaeLocale = $semestre->getNbHeuresEnseignementSaeLocale();
        $this->nbHeuresEnseignementRessourceLocale = $this->nbHeuresEnseignementLocale - $this->nbHeuresEnseignementSaeLocale;
        $this->nbHeuresEnseignementRessourceNational = $this->nbHeuresRessourcesSae - $this->nbHeuresEnseignementLocale;

        $this->nbHeuresTpLocale = $semestre->getNbHeuresTpLocale();
        $this->nbHeuresTpNational = $semestre->getNbHeuresTpNational();
        $this->nbSemaines = $semestre->getNbSemaines();
        $this->nbSemainesConges = $semestre->getNbSemainesConges();
        $this->nbSemainesStageMin = $semestre->getNbSemaineStageMin();
        $this->nbSemainesStageMax = $semestre->getNbSemainesStageMax();
        $this->nbSemainesCoursProjet = $this->nbSemaines - $this->nbSemainesConges - $this->nbSemainesStageMax;
        $this->nbHeuresProjet = $semestre->getNbHeuresProjet();
        $this->nbHeuresCoursProjet = $this->nbHeuresRessourcesSae + $this->nbHeuresProjet;
        $this->nbDemiJournees = $semestre->getNbDemiJournees();
        $this->dureeHebdo = $this->nbSemainesCoursProjet !== 0 ? $this->nbHeuresCoursProjet / $this->nbSemainesCoursProjet : 0;
        $this->nbMoyenneHeuresDemiJournee = $this->nbDemiJournees !== 0.0 ? $this->dureeHebdo / $this->nbDemiJournees : 0;
        $this->nbHeuresCoursHebdo = $this->nbSemainesCoursProjet !== 0 ? $this->nbHeuresRessourcesSae / $this->nbSemainesCoursProjet : 0;
        $this->nbHeuresHebdoProjet = $this->nbSemainesCoursProjet !== 0 ? $this->nbHeuresProjet / $this->nbSemainesCoursProjet : 0;

    }

    public function getJson()
    {
        return [
            'nbHeuresRessourcesSae' => number_format($this->nbHeuresRessourcesSae, 2),
            'pourcentageAdaptationLocale' => number_format($this->pourcentageAdaptationLocale, 2),
            'nbHeuresEnseignementLocale' => number_format($this->nbHeuresEnseignementLocale, 2),
            'nbHeuresEnseignementSaeLocale' => number_format($this->nbHeuresEnseignementSaeLocale, 2),
            'nbHeuresEnseignementRessourceLocale' => number_format($this->nbHeuresEnseignementRessourceLocale, 2),
            'nbHeuresEnseignementRessourceNational' => number_format($this->nbHeuresEnseignementRessourceNational, 2),
            'nbSemaines' => $this->nbSemaines,
            'nbSemainesConges' => $this->nbSemainesConges,
            'nbSemainesStageMin' => $this->nbSemainesStageMin,
            'nbSemainesStageMax' => $this->nbSemainesStageMax,
            'nbSemainesCoursProjet' => $this->nbSemainesCoursProjet,
            'nbHeuresProjet' => number_format($this->nbHeuresProjet, 2),
            'nbHeuresCoursProjet' => number_format($this->nbHeuresCoursProjet, 2),
            'nbDemiJournees' => $this->nbDemiJournees,
            'dureeHebdo' => number_format($this->dureeHebdo, 2),
            'nbMoyenneHeuresDemiJournee' => number_format($this->nbMoyenneHeuresDemiJournee, 2),
            'nbHeuresCoursHebdo' => number_format($this->nbHeuresCoursHebdo, 2),
            'nbHeuresHebdoProjet' => number_format($this->nbHeuresHebdoProjet, 2),
            'nbHeuresTpNational' => number_format($this->nbHeuresTpNational, 2),
            'nbHeuresTpLocale' => number_format($this->nbHeuresTpLocale, 2)
        ];
    }

}
