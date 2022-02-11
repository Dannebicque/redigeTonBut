<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Classes/Apc/ApcStructure.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 18/05/2021 21:39
 */

/*
 * Pull your hearder here, for exemple, Licence header.
 */

namespace App\Classes\Apc;

use App\Entity\Departement;
use App\Repository\ApcParcoursNiveauRepository;

class ApcStructure
{

    private ApcParcoursNiveauRepository $apcParcoursNiveauRepository;


    public function __construct(ApcParcoursNiveauRepository $apcParcoursNiveauRepository)
    {
        $this->apcParcoursNiveauRepository = $apcParcoursNiveauRepository;
    }



    public function parcoursNiveaux(Departement $departement): array
    {
        $tParcours = [];
        foreach ($departement->getApcParcours() as $parcours) {
            $pn = $this->apcParcoursNiveauRepository->findParcoursNiveauCompetence($parcours);
            $tParcours[$parcours->getId()] = [];
            foreach ($pn as $niveau) {
                if (null !== $niveau && null !== $niveau->getNiveau()) {
                    $niv = $niveau->getNiveau();
                    if (null !== $niv && null !== $niv->getCompetence() && !\array_key_exists($niv->getCompetence()->getId(),
                            $tParcours[$parcours->getId()])) {
                        $tParcours[$parcours->getId()][$niv->getCompetence()->getId()] = [];
                    }
                    if (null !== $niv->getAnnee()) {
                        $tParcours[$parcours->getId()][$niv->getCompetence()->getId()][$niv->getAnnee()->getOrdre()] = $niv;
                    }
                }
            }
        }

        return $tParcours;
    }
}
