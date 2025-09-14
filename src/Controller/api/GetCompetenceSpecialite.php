<?php

namespace App\Controller\api;

use App\Repository\ApcComptenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetCompetenceSpecialite extends AbstractController
{
    private \App\Repository\ApcComptenceRepository $apcComptenceRepository;

    public function __construct(ApcComptenceRepository $apcComptenceRepository)
    {
        $this->apcComptenceRepository = $apcComptenceRepository;
    }

    public function __invoke(Request $request, $data): array
    {
        return $this->apcComptenceRepository->findBySigleDepartement($request->get('specialite'));
    }
}
