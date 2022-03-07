<?php

namespace App\Controller;

use App\Classes\Word\MyWord;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportWordController extends AbstractController
{
    private MyWord $myWord;

    public function __construct(
        MyWord $myWord,
    )
    {
        $this->myWord = $myWord;
    }


    #[Route('/export/word/sae/{sae}', name: 'export_word_sae')]
    public function exportWordSae(ApcSae $sae): Response
    {
        return $this->myWord->exportSae($sae);
    }

    #[Route('/export/word/ressource/{ressource}', name: 'export_word_ressource')]
    public function exportWordRessource(ApcRessource $ressource): Response
    {
        return $this->myWord->exportRessource($ressource);
    }
}
