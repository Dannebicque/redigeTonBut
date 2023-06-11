<?php

namespace App\Controller\api;

use App\Repository\ApcComptenceRepository;
use App\Repository\ApcRessourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class GetRessourcesSpecialite extends AbstractController
{
    private $apcRessourceRepository;

    public function __construct(ApcRessourceRepository $apcRessourceRepository)
    {
        $this->apcRessourceRepository = $apcRessourceRepository;
    }

    public function __invoke(Request $request, $data)
    {
        return $this->apcRessourceRepository->findBySigleDepartement($request->get('specialite'));
    }
}
