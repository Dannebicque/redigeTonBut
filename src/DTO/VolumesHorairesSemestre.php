<?php


namespace App\DTO;


use App\Entity\Semestre;

class VolumesHorairesSemestre
{
    public Semestre $semestre;
    public array $ressources = [];
    public float $vhNbHeuresEnseignementSae = 0;
    public float $vhNbHeureeEnseignementSaeRessource = 0;
    public float $vhNbHeuresDontTpSaeRessource = 0;
    public float $vhNbHeuresProjetTutores = 0;
    public float $totalEnseignementRessources = 0;
    public float $totalDontTpRessources = 0;
    public float $totalAdaptationLocaleEnseignement = 0;
    public float $totalAdaptationLocaleDontTp = 0;
    public float $totalEnseignements = 0;
    public float $totalDontTp = 0;
    public float $totalProjetTutore = 0;
    public float $totalEnseignementProjetTutore = 0;

    public float $cibleNbHeureeEnseignementSaeRessource = 0;
    public float $cibleNbHeureProjet = 0;
    public float $cibleNbHeureTotal = 0;
    private float $cibleNbHeureTpTotal = 0;


    public function __construct(Semestre $semestre, $ressources)
    {
        /** @var \App\Entity\ApcRessource $ressource */
        foreach ($ressources as $ressource) {
            $this->ressources[$ressource->getId()]['totalEnseignement'] = $ressource->getHeuresTotales();
            $this->ressources[$ressource->getId()]['dontTp'] = $ressource->getTpPpn();
            $this->totalEnseignementRessources += $ressource->getHeuresTotales();
            $this->totalDontTpRessources += $ressource->getTpPpn();
        }

        $this->vhNbHeuresEnseignementSae = $semestre->getNbHeuresEnseignementSaeLocale();
        $this->vhNbHeureeEnseignementSaeRessource = $semestre->getNbHeuresEnseignementRessourceLocale();
        $this->vhNbHeuresDontTpSaeRessource = $semestre->getNbHeuresTpLocale();
        $this->vhNbHeuresProjetTutores = $semestre->getNbHeuresProjet();

        $this->totalAdaptationLocaleEnseignement = $this->vhNbHeuresEnseignementSae + $this->vhNbHeureeEnseignementSaeRessource;
        $this->totalAdaptationLocaleDontTp = $this->vhNbHeuresDontTpSaeRessource;
        $this->totalEnseignements = $this->totalEnseignementRessources + $this->vhNbHeuresEnseignementSae + $this->vhNbHeureeEnseignementSaeRessource;
        $this->totalDontTp = $this->vhNbHeuresDontTpSaeRessource +  $this->totalDontTpRessources;
        $this->totalProjetTutore = $this->vhNbHeuresProjetTutores;
        $this->totalEnseignementProjetTutore = $this->totalEnseignements + $this->totalProjetTutore;

        // Cible
        $this->cibleNbHeureeEnseignementSaeRessource = $semestre->getNbHeuresRessourceSae();
        $this->cibleNbHeureProjet = $semestre->getNbHeuresProjet();
        $this->cibleNbHeureTotal = $this->cibleNbHeureeEnseignementSaeRessource + $this->cibleNbHeureProjet;
        $this->cibleNbHeureTpTotal = $semestre->getNbHeuresTpLocale()+ $semestre->getNbHeuresTpNational();

    }

    public function getJson()
    {
        return [
            'ressources' => $this->ressources,
            'vhNbHeuresEnseignementSae' => $this->vhNbHeuresEnseignementSae,
            'vhNbHeureeEnseignementSaeRessource' => $this->vhNbHeureeEnseignementSaeRessource,
            'vhNbHeuresDontTpSaeRessource' => $this->vhNbHeuresDontTpSaeRessource,
            'vhNbHeuresProjetTutores' => $this->vhNbHeuresProjetTutores,
            'totalEnseignementRessources' => $this->totalEnseignementRessources,
            'totalDontTpRessources' => $this->totalDontTpRessources,
            'pourcentageAdaptationLocaleCalcule' => $this->getPourcentageAdaptationLocaleCalcule(),
            'pourcentageTpNational' => $this->getPourcentageTpNational(),
            'pourcentageTpLocalement' => $this->getPourcentageTpLocalement(),
            'pourcentageTpLocalementNationalement' => $this->getPourcentageTpLocalementNationalement(),
            'totalAdaptationLocaleEnseignement' => $this->totalAdaptationLocaleEnseignement,
            'totalAdaptationLocaleDontTp' => $this->totalAdaptationLocaleDontTp,
            'totalEnseignements' => $this->totalEnseignements,
            'totalDontTp' => $this->totalDontTp,

            'totalProjetTutore' => $this->totalProjetTutore,
            "totalEnseignementProjetTutore" => $this->totalEnseignementProjetTutore,
            "cibleNbHeureeEnseignementSaeRessource" => $this->cibleNbHeureeEnseignementSaeRessource,
            "cibleNbHeureProjet" => $this->cibleNbHeureProjet,
            "cibleNbHeureTotal" => $this->cibleNbHeureTotal,
            'cibleNbHeureTpTotal' => $this->cibleNbHeureTpTotal
        ];
    }

    private function getPourcentageAdaptationLocaleCalcule()
    {
        return $this->totalEnseignements !== 0.0 ? $this->totalAdaptationLocaleEnseignement / $this->totalEnseignements * 100 : 0;
    }

    private function getPourcentageTpNational()
    {
        return $this->totalEnseignementRessources !== 0.0 ? $this->totalDontTpRessources / $this->totalEnseignementRessources * 100 : 0;
    }

    private function getPourcentageTpLocalement()
    {
        return $this->totalAdaptationLocaleEnseignement !== 0.0 ? $this->totalAdaptationLocaleDontTp / $this->totalAdaptationLocaleEnseignement * 100 : 0;
    }

    private function getPourcentageTpLocalementNationalement()
    {
        return $this->totalAdaptationLocaleEnseignement + $this->totalEnseignementRessources !== 0.0 ? ($this->totalAdaptationLocaleDontTp + $this->totalDontTpRessources) / ($this->totalAdaptationLocaleEnseignement + $this->totalEnseignementRessources) * 100 : 0;
    }

}
