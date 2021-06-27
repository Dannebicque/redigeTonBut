<?php


namespace App\DTO;


use App\Entity\Semestre;

class PreconisationSemestre
{
    private array $tCompetences = [];
    private array $tRessources = [];
    private array $tSaes = [];

    public function __construct(Semestre $semestre, array $competences)
    {
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
        }

        //coeff ressources/SAE par compétences
        $saes = $semestre->getApcSaes();
        $ressources = $semestre->getApcRessources();

        foreach ($saes as $sae) {
            $this->tSaes[$sae->getId()] = [];
            $this->tSaes[$sae->getId()]['total'] = 0;
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


    }

    public function getJson()
    {
        return [
            'saes' => $this->tSaes,
            'ressources' => $this->tRessources,
            'competences' => $this->tCompetences
        ];
    }
}
