<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Controller/administration/apc/ApcParcoursController.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:20
 */

namespace App\Controller\competences;

use App\Controller\BaseController;
use App\Entity\ApcNiveau;
use App\Entity\ApcParcours;
use App\Entity\ApcParcoursNiveau;
use App\Entity\Diplome;
use App\Repository\ApcComptenceRepository;
use App\Repository\ApcParcoursNiveauRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/apc/parcours-niveau", name: "administration_")]
class ApcParcoursNiveauController extends BaseController
{
    #[Route(path: '/configuration/{parcours}', name: 'parcours_niveau_config', methods: ['GET', 'POST'])]
    public function config(
        ApcParcoursNiveauRepository $apcParcoursNiveauRepository,
        ApcComptenceRepository      $apcComptenceRepository,
        ApcParcours                 $parcours
    ): Response
    {
        $competences = $apcComptenceRepository->findByDepartement($this->getDepartement());
        $tabNiveaux = $apcParcoursNiveauRepository->findNiveauByParcoursArray($parcours);

        return $this->render('competences/apc_parcours_niveau/configuration.html.twig', [
            'parcours' => $parcours,
            'comptences' => $competences,
            'tabNiveauxId' => $tabNiveaux,
            'departement' => $this->getDepartement()
        ]);
    }

    #[Route(path: '/ajax/{parcours}/{etat}/{niveau}', name: 'apc_parcours_niveau_ajax', options: ['expose' => true])]
    public function ajax(ApcParcoursNiveauRepository $apcParcoursNiveauRepository, ApcParcours $parcours, $etat, ApcNiveau $niveau): Response
    {
        if (0 == $etat) {
            // existe et on souhaite retirer
            $pn = $apcParcoursNiveauRepository->findParcoursNiveau($parcours, $niveau);
            if ($pn) {
                $this->entityManager->remove($pn);
            }
        } else {
            // n'existe pas on ajoute
            $pn = new ApcParcoursNiveau();
            $pn->setNiveau($niveau);
            $pn->setParcours($parcours);
            $this->entityManager->persist($pn);
        }
        $this->entityManager->flush();

        return $this->json(true);
    }
}
