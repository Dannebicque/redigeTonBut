<?php

namespace App\Controller\api;

use App\Repository\ApcComptenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetCompetenceSpecialite extends AbstractController
{
    private $apcComptenceRepository;

    public function __construct(ApcComptenceRepository $apcComptenceRepository)
    {
        $this->apcComptenceRepository = $apcComptenceRepository;
    }

    public function __invoke(Request $request, $data)
    {
        return $this->apcComptenceRepository->findBySigleDepartement($request->get('specialite'));
    }
}
