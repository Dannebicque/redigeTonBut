<?php


namespace App\DTO;


use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Semestre;

class PreconisationSemestre
{
    private array $tCompetences = [];
    private array $tRessources = [];
    private array $tRessourcesAl = [];
    private array $tSaes = [];
    private array $tSaesAl = [];
    private array $tSemestre = [];

    public function __construct(Semestre $semestre, array $competences, array $ressources, array $saes, array $ressourcesAl, array $saesAl, ?ApcParcours $parcours = null)
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
            $this->tCompetences[$competence->getId()]['totalAl'] = 0;
            $this->tCompetences[$competence->getId()]['sae'] = 0;
            $this->tCompetences[$competence->getId()]['saeAl'] = 0;
            $this->tCompetences[$competence->getId()]['ressource'] = 0;
            $this->tCompetences[$competence->getId()]['ressourceAl'] = 0;
            $this->tCompetences[$competence->getId()]['rapport'] = 0;
            $this->tCompetences[$competence->getId()]['rapportAl'] = 0;
            $this->tCompetences[$competence->getId()]['ects'] = 0;

        }

        foreach ($semestre->getApcCompetenceSemestres() as $apc) {
            if (array_key_exists($apc->getCompetence()->getId(), $this->tCompetences)) {
                if ($parcours !== null && $semestre->getDepartement()->getTypeStructure() !== Departement::TYPE3) {
                    //On est donc en S3->S6, hors type 3. Donc on peut différencier les ECTS
                    $this->tCompetences[$apc->getCompetence()->getId()]['ects'] = $apc->getEctsParcours() !== null ? $apc->getEctsParcours()[$parcours->getId()] : 0;
                    $this->tSemestre['nb_ects'] += $apc->getEctsParcours() !== null ? $apc->getEctsParcours()[$parcours->getId()] : 0;
                } else {
                    //si type 3 ou S1/S2 on reste sur ECTS unique.
                    $this->tCompetences[$apc->getCompetence()->getId()]['ects'] = $apc->getECTS();
                    $this->tSemestre['nb_ects'] += $apc->getECTS();
                }
            }
        }

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
                if (array_key_exists($comp->getCompetence()->getId(),
                    $this->tCompetences)) {

                    if (!array_key_exists($comp->getCompetence()->getId(), $this->tSaes[$sae->getId()])) {
                        $this->tSaes[$sae->getId()][$comp->getCompetence()->getId()] = [];
                    }
                    $this->tSaes[$sae->getId()][$comp->getCompetence()->getId()]['coefficient'] = $comp->getCoefficient();
                    $this->tSaes[$sae->getId()]['total'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['total'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['totalAl'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['sae'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['saeAl'] += $comp->getCoefficient();
                }
            }
        }

        foreach ($saesAl as $sae) {
            $this->tSaesAl[$sae->getId()] = [];
            $this->tSaesAl[$sae->getId()]['total'] = 0;
            $this->tSaesAl[$sae->getId()]['heures_totales'] = $sae->getHeuresTotales();
            $this->tSaesAl[$sae->getId()]['heures_tp'] = $sae->getTpPpn();
            $this->tSaesAl[$sae->getId()]['heures_projet'] = $sae->getProjetPpn();

//            $this->tSemestre['total_hors_projet'] += $this->tSaes[$sae->getId()]['heures_totales'];
//            $this->tSemestre['dont_tp'] += $this->tSaes[$sae->getId()]['heures_tp'];
//            $this->tSemestre['total_h_projet'] += $this->tSaes[$sae->getId()]['heures_projet'];
//            $this->tSemestre['pratique'] += $this->tSaes[$sae->getId()]['heures_totales'] + $this->tSaes[$sae->getId()]['heures_projet'];


            foreach ($sae->getApcSaeCompetences() as $comp) {
                if (array_key_exists($comp->getCompetence()->getId(),
                    $this->tCompetences)) {

                    if (!array_key_exists($comp->getCompetence()->getId(), $this->tSaesAl[$sae->getId()])) {
                        $this->tSaesAl[$sae->getId()][$comp->getCompetence()->getId()] = [];
                    }
                    $this->tSaesAl[$sae->getId()][$comp->getCompetence()->getId()]['coefficient'] = $comp->getCoefficient();
                    $this->tSaesAl[$sae->getId()]['total'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['totalAl'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['saeAl'] += $comp->getCoefficient();
                }
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
                if (array_key_exists($comp->getCompetence()->getId(),
                    $this->tCompetences)) {
                    if (!array_key_exists($comp->getCompetence()->getId(), $this->tRessources[$ressource->getId()])) {
                        $this->tRessources[$ressource->getId()][$comp->getCompetence()->getId()] = [];
                    }

                    $this->tRessources[$ressource->getId()][$comp->getCompetence()->getId()]['coefficient'] = $comp->getCoefficient();
                    $this->tRessources[$ressource->getId()]['total'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['total'] += (float)$comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['totalAl'] += (float)$comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['ressource'] += (float)$comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['ressourceAl'] += (float)$comp->getCoefficient();
                }
            }
        }

        foreach ($ressourcesAl as $ressource) {
            $this->tRessourcesAl[$ressource->getId()] = [];
            $this->tRessourcesAl[$ressource->getId()]['total'] = 0;
            $this->tRessourcesAl[$ressource->getId()]['heures_totales'] = $ressource->getHeuresTotales();
            $this->tRessourcesAl[$ressource->getId()]['heures_tp'] = $ressource->getTpPpn();

//            $this->tSemestre['total_hors_projet'] += $this->tRessources[$ressource->getId()]['heures_totales'];
//            $this->tSemestre['dont_tp'] += $this->tRessources[$ressource->getId()]['heures_tp'];
//            $this->tSemestre['pratique'] += $this->tRessources[$ressource->getId()]['heures_tp'];

            foreach ($ressource->getApcRessourceCompetences() as $comp) {
                if (array_key_exists($comp->getCompetence()->getId(),
                    $this->tCompetences)) {
                    if (!array_key_exists($comp->getCompetence()->getId(), $this->tRessourcesAl[$ressource->getId()])) {
                        $this->tRessourcesAl[$ressource->getId()][$comp->getCompetence()->getId()] = [];
                    }

                    $this->tRessourcesAl[$ressource->getId()][$comp->getCompetence()->getId()]['coefficient'] = $comp->getCoefficient();
                    $this->tRessourcesAl[$ressource->getId()]['total'] += $comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['totalAl'] += (float)$comp->getCoefficient();
                    $this->tCompetences[$comp->getCompetence()->getId()]['ressourceAl'] += (float)$comp->getCoefficient();
                }
            }
        }

        foreach ($competences as $competence) {
            if ($this->tCompetences[$competence->getId()]['total'] != 0) {
                $this->tCompetences[$competence->getId()]['rapport'] = (float)$this->tCompetences[$competence->getId()]['sae'] / (float)$this->tCompetences[$competence->getId()]['total'];
            }

            if ($this->tCompetences[$competence->getId()]['totalAl'] != 0) {
                $this->tCompetences[$competence->getId()]['rapportAl'] = (float)$this->tCompetences[$competence->getId()]['saeAl'] / (float)$this->tCompetences[$competence->getId()]['totalAl'];
            }
        }

        $this->tSemestre['total_vol_dont_prj'] = $this->tSemestre['total_hors_projet'] + $this->tSemestre['total_h_projet'];
        if ($this->tSemestre['total_vol_dont_prj'] > 0) {
            $this->tSemestre['rapport'] = $this->tSemestre['pratique'] / $this->tSemestre['total_vol_dont_prj'];
        }
    }

    public function getJson()
    {
        return [
            'saes' => $this->tSaes,
            'saesAl' => $this->tSaesAl,
            'ressources' => $this->tRessources,
            'ressourcesAl' => $this->tRessourcesAl,
            'competences' => $this->tCompetences,
            'semestre' => $this->tSemestre
        ];
    }
}
