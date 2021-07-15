<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcRessourceController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 19/05/2021 16:40
 */

namespace App\Controller\competences;

use App\Controller\BaseController;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcRessourceCompetenceRepository;
use App\Repository\SemestreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/competence/api/competence", name="competence_")
 */
class ApcAjaxCompetenceController extends BaseController
{
    /**
     * @Route("/ressource/ajax-semestre", name="apc_competences_ressource_semestre_ajax", methods={"POST"}, options={"expose":true})
     */
    public function ajaxCompetencesSemestreRessource(
        SemestreRepository $semestreRepository,
        ApcRessourceCompetenceRepository $apcRessourceCompetenceRepository,
        ApcNiveauRepository $apcNiveauRepository,
        Request $request
    ): Response {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        $semestre = $semestreRepository->find($parametersAsArray['semestre']);

        if (null !== $semestre) {
            $competences = $apcNiveauRepository->findBySemestre($semestre);

            $resComp = $apcRessourceCompetenceRepository->findByRessourceArray(['ressource' => $parametersAsArray['ressource']]);
            if ($competences !== null) {

                $t = [];
                foreach ($competences as $d) {
                    $b = [];

                    $b['id'] = $d->getCompetence()->getId();
                    $b['libelle'] = $d->getCompetence()->getLibelle();
                    $b['checked'] = true === in_array($d->getCompetence()->getId(), $resComp);
                    $t[] = $b;
                }

                return $this->json($t);
            }
        }

        return $this->json(false);
    }
}
