<?php

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Classes\Mcc;
use App\Repository\ApcParcoursRepository;
use App\Repository\DepartementRepository;
use App\Repository\VersionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mcc/export', name: 'export_mcc_')]
class ExportMCCController extends BaseController
{
    private ExcelWriter $excelWriter;

    public function __construct(ExcelWriter $excelWriter) {
        $this->excelWriter = $excelWriter;
    }

    #[Route('/', name: 'index')]
    public function index(
        VersionRepository $versionRepository
    ): Response
    {
        return $this->render('export/mcc/index.html.twig', [
            'versions' => $versionRepository->findAll(),
        ]);
    }

    #[Route('/parcours', name: 'parcours')]
    public function getParcours(VersionRepository $versionRepository, Request $request): Response
    {
        //todo: passer par Version ?
        $version = $versionRepository->find($request->query->get('departement'));
        if ($version !== null) {
            return $this->render('export/mcc/parcours.html.twig', [
                'parcours' => $version->getApcParcours(),
            ]);
        }
    }

    #[Route('/genere', name: 'genere')]
    public function genere(
        ApcParcoursRepository $apcParcoursRepository,
        VersionRepository $departementRepository,
        Mcc $mcc, Request $request): Response
    {
        $version = $departementRepository->find($request->request->get('departement'));
        $parcours = [];
        $formParcours = $request->request->all()['parcours'];
        foreach ($formParcours as $id) {
            $parcours[$id] = $apcParcoursRepository->find($id);
        }

        if ($version !== null) {
            $iut = $request->request->get('iut');
            $type = $request->request->get('type');

            return $mcc->genereFichierExcel($this->excelWriter, $version, $iut, $parcours, $type === 'fi');
        }
    }
}
