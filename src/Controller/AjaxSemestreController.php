<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcRessourceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 19/05/2021 16:40
 */

namespace App\Controller;

use App\Entity\Semestre;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/semestre/api/", name="semestre_")
 */
class AjaxSemestreController extends BaseController
{

    #[Route("/{semestre}/{type}/update_heures_ajax", name:"heure_update_ajax", methods:["POST"],  options:["expose" =>true])]
    public function updateHeures(
        Request $request,
        Semestre $semestre,
        string $type
    ) {
        if ($this->getDepartement()->getVerouilleStructure() === false) {
            $parametersAsArray = [];
            if ($content = $request->getContent()) {
                $parametersAsArray = json_decode($content, true);
            }

            switch ($type) {
                case 'vhNbHeuresEnseignementSae':
                    $semestre->setNbHeuresEnseignementSaeLocale($parametersAsArray['valeur']);
                    break;
                case 'vhNbHeureeEnseignementSaeRessource':
                    $semestre->setNbHeuresEnseignementRessourceLocale($parametersAsArray['valeur']);
                    break;
                case 'vhNbHeuresDontTpSaeRessource':
                    $semestre->setNbHeuresTpLocale($parametersAsArray['valeur']);
                    break;
                case 'vhNbHeuresProjetTutores':
                    $semestre->setNbHeuresProjet($parametersAsArray['valeur']);
                    break;
            }
            $this->entityManager->flush();

            return $this->json(true);
        }
        return $this->json(false);
    }
}
