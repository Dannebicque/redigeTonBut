<?php

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Repository\ApcRessourceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Route('/ressources-ia', name: 'ressources_ia_')]
class RessourceIAController extends BaseController
{
    #[Route('/', name: 'index')]
    public function index(ApcRessourceRepository $apcRessourceRepository): Response
    {
        $ressources = $apcRessourceRepository->findRessourceIA();
        return $this->render('ressource_ia/index.html.twig', [
            'ressources' => $ressources
        ]);
    }

    #[Route('/export', name: 'export')]
    public function export(ApcRessourceRepository $apcRessourceRepository, ExcelWriter $excelWriter): StreamedResponse
    {
        $ressources = $apcRessourceRepository->findRessourceIA();

        $excelWriter->nouveauFichier('');
        $excelWriter->createSheet('Ressources IA');
        $excelWriter->writeCellName('A1', 'Titre');
        $excelWriter->writeCellName('B1', 'Semestre');
        $excelWriter->writeCellName('C1', 'Spécialité');
        $excelWriter->writeCellName('D1', 'Parcours');
        $excelWriter->writeCellName('E1', 'Mots Clés');
        $excelWriter->writeCellName('F1', 'Description');
        $row = 2;
        foreach ($ressources as $ressource) {
            $excelWriter->writeCellName('A' . $row, $ressource->getLibelle());
            $excelWriter->writeCellName('B' . $row, $ressource->getSemestre()?->getLibelle());
            $excelWriter->writeCellName('C' . $row, $ressource->getVersion()?->getDepartement()?->getSigle());
            $excelWriter->writeCellName('D' . $row, $ressource->getSemestre()?->getApcParcours()?->getLibelle());
            $excelWriter->writeCellName('E' . $row, $ressource->getMotsCles());
            $excelWriter->writeCellName('F' . $row, $ressource->getDescription());
            $row++;
        }

        return $excelWriter->genereFichier('ressources_ia.xlsx');
    }
}
