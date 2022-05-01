<?php

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Classes\Mcc;
use App\Classes\Tableau\Structure;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Repository\ApcParcoursRepository;
use App\Repository\DepartementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mcc/export', name: 'export_mcc_')]
class ExportMCCController extends BaseController
{
    private ExcelWriter $excelWriter;

    public function __construct(ExcelWriter $excelWriter) {
        $this->excelWriter = $excelWriter;
    }

    #[Route('/', name: 'index')]
    public function index(
        DepartementRepository $departementRepository
    ): Response
    {
        return $this->render('export/mcc/index.html.twig', [
            'departements' => $departementRepository->findAll(),
        ]);
    }

    #[Route('/parcours', name: 'parcours')]
    public function getParcours(DepartementRepository $departementRepository, Request $request): Response
    {
        $departement = $departementRepository->find($request->query->get('departement'));
        if ($departement !== null) {
            return $this->render('export/mcc/parcours.html.twig', [
                'parcours' => $departement->getApcParcours(),
            ]);
        }
    }

    #[Route('/genere', name: 'genere')]
    public function genere(
        ApcParcoursRepository $apcParcoursRepository,
        DepartementRepository $departementRepository,
        Mcc $mcc, Request $request): Response
    {
        $departement = $departementRepository->find($request->request->get('departement'));
        $parcours = [];
        $formParcours = $request->request->get('parcours');
        foreach ($formParcours as $id) {
            $parcours[$id] = $apcParcoursRepository->find($id);
        }

        if ($departement !== null) {
            $iut = $request->request->get('iut');
            $type = $request->request->get('type');

            return $mcc->genereFichierExcel($this->excelWriter, $departement, $iut, $parcours, $type === 'fi');
        }
    }
}
