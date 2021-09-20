<?php

namespace App\Controller;

use App\Classes\Excel\ExcelWriter;
use App\Classes\Tableau\Structure;
use App\Entity\ApcParcours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/export/excel/', name: 'export_excel_')]
class ExportExcelController extends BaseController
{
    private ExcelWriter $excelWriter;

    public function __construct(ExcelWriter $excelWriter) {
        $this->excelWriter = $excelWriter;
    }

    #[Route('/structure/{parcours}', name: 'structure')]
    public function structure(Structure $structure, ApcParcours $parcours = null): Response
    {
        return $structure->genereFichierExcel($this->excelWriter, $this->getDepartement(), $parcours);
    }
}
