<?php

namespace App\Controller\api;

use App\Repository\ApcComptenceRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetSaesSpecialite extends AbstractController
{
    private $apcSaeRepository;

    public function __construct(ApcSaeRepository $apcSaeRepository)
    {
        $this->apcSaeRepository = $apcSaeRepository;
    }

    public function __invoke(Request $request, $data)
    {
        return $this->apcSaeRepository->findBySigleDepartement($request->get('specialite'));
    }
}
