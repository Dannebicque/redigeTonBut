<?php


namespace App\DTO;


use App\Entity\Semestre;

class PreconisationSemestre
{
    private array $tCompetences = [];
    private array $tRessources = [];
    private array $tSaes = [];
    private array $tSemestre = [];

    public function __construct(Semestre $semestre, array $competences)
    {
        $this->tSemestre['total_hors_projet'] = 0;
        $this->tSemestre['dont_tp'] = 0;
        $this->tSemestre['total_h_projet'] = 0;
        $this->tSemestre['total_vol_dont_prj'] = 0;
        $this->tSemestre['rapport'] = 0;
        $this->tSemestre['pratique'] = 0;
        $this->tSemestre['nb_ects'] = 0;



        foreach ($competences as $competence) {
            $this->tCompetences[$competence->getId()] = [];
            $this->tCompetences[$competence->getId()]['total'] = 0;
            $this->tCompetences[$competence->getId()]['sae'] = 0;
            $this->tCompetences[$competence->getId()]['ressource'] = 0;
            $this->tCompetences[$competence->getId()]['rapport'] = 0;
            $this->tCompetences[$competence->getId()]['ects'] = 0;
        }

        foreach ($semestre->getApcCompetenceSemestres() as $apc)
        {
            $this->tCompetences[$apc->getCompetence()->getId()]['ects'] = $apc->getECTS();
            $this->tSemestre['nb_ects'] += $apc->getECTS();
        }

        //coeff ressources/SAE par compétences
        $saes = $semestre->getApcSaes();
        $ressources = $semestre->getApcRessources();

        foreach ($saes as $sae) {
            $this->tSaes[$sae->getId()] = [];
            $this->tSaes[$sae->getId()]['total'] = 0;
            $this->tSaes[$sae->getId()]['heures_totales'] = $sae->getHeuresTotales();
            $this->tSaes[$sae->getId()]['heures_tp'] = $sae->getTpPpn();
            $this->tSaes[$sae->getId()]['heures_projet'] = $sae->getProjetPpn();

            $this->tSemestre['total_hors_projet'] += $this->tSaes[$sae->getId()]['heures_totales'];
            $this->tSemestre['dont_tp'] += $this->tSaes[$sae->getId()]['heures_tp'];
            $this->tSemestre['total_h_projet'] += $this->tSaes[$sae->getId()]['heures_projet'];
            $this->tSemestre['pratique'] += $this->tSaes[$sae->getId()]['heures_totales'] + $this->tSaes[$sae->getId()]['heures_projet'];


            foreach ($sae->getApcSaeCompetences() as $comp) {
                $this->tSaes[$sae->getId()][$comp->getCompetence()->getId()]['coefficient'] = $comp->getCoefficient();
                $this->tSaes[$sae->getId()]['total'] += $comp->getCoefficient();
                $this->tCompetences[$comp->getCompetence()->getId()]['total'] += $comp->getCoefficient();
                $this->tCompetences[$comp->getCompetence()->getId()]['sae'] += $comp->getCoefficient();
            }
        }
        foreach ($ressources as $ressource) {
            $this->tRessources[$ressource->getId()] = [];
            $this->tRessources[$ressource->getId()]['total'] = 0;
            $this->tRessources[$ressource->getId()]['heures_totales'] = $ressource->getHeuresTotales();
            $this->tRessources[$ressource->getId()]['heures_tp'] = $ressource->getTpPpn();

            $this->tSemestre['total_hors_projet'] += $this->tRessources[$ressource->getId()]['heures_totales'];
            $this->tSemestre['dont_tp'] += $this->tRessources[$ressource->getId()]['heures_tp'];
            $this->tSemestre['pratique'] += $this->tRessources[$ressource->getId()]['heures_tp'];

            foreach ($ressource->getApcRessourceCompetences() as $comp) {
                $this->tRessources[$ressource->getId()][$comp->getCompetence()->getId()]['coefficient'] = $comp->getCoefficient();
                $this->tRessources[$ressource->getId()]['total'] += $comp->getCoefficient();
                $this->tCompetences[$comp->getCompetence()->getId()]['total'] += $comp->getCoefficient();
                $this->tCompetences[$comp->getCompetence()->getId()]['ressource'] += $comp->getCoefficient();
            }
        }

        foreach ($competences as $competence) {

            if ($this->tCompetences[$competence->getId()]['total'] !== 0) {
                $this->tCompetences[$competence->getId()]['rapport'] = number_format($this->tCompetences[$competence->getId()]['sae'] / $this->tCompetences[$competence->getId()]['total'], 2);
            }
        }

        $this->tSemestre['total_vol_dont_prj'] = $this->tSemestre['total_hors_projet'] + $this->tSemestre['total_h_projet'];
        if ( $this->tSemestre['total_vol_dont_prj'] > 0) {
            $this->tSemestre['rapport'] = $this->tSemestre['pratique'] / $this->tSemestre['total_vol_dont_prj'];
        }
    }

    public function getJson()
    {
        return [
            'saes' => $this->tSaes,
            'ressources' => $this->tRessources,
            'competences' => $this->tCompetences,
            'semestre' => $this->tSemestre
        ];
    }
}
