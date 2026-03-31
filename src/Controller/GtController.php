<?php

namespace App\Controller;

use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_GT')]
class GtController extends BaseController
{
    #[Route('/gt', name: 'gt_index')]
    public function index(
        DepartementRepository $departementRepository
    ): Response
    {
        $departements = $departementRepository->findAll();
        $currentDepartement = $this->getDepartement();

        $parcours = [];
        if ($currentDepartement instanceof Departement) {
            $version = $this->getVersion();
            if ($version) {
                $parcours = $version->getApcParcours();
            }
        } else {
            $currentDepartement = null;
        }

        return $this->render('gt/index.html.twig', [
            'departements' => $departements,
            'currentDepartement' => $currentDepartement ,
            'parcours' => $parcours,
        ]);
    }
}
