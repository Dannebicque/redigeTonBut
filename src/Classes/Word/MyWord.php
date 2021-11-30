<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Classes/Word/MyWord.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 25/06/2021 10:28
 */

namespace App\Classes\Word;

use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Repository\ApcSaeRessourceRepository;
use Parsedown;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class MyWord
{
    private string $dir;
    private ApcSaeRessourceRepository $apcSaeRessourceRepository;

    public function __construct(
        ApcSaeRessourceRepository $apcSaeRessourceRepository,
        KernelInterface $kernel)
    {
        $this->dir = $kernel->getProjectDir() . '/public/word/';
        Settings::setOutputEscapingEnabled(true);
        $this->apcSaeRessourceRepository = $apcSaeRessourceRepository;
    }

    /**
     * @return StreamedResponse
     *
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function exportSae(ApcSae $apcSae)
    {
        $templateProcessor = $this->genereWordSae($apcSae);

        $filename = 'sae_' . $apcSae->getCodeMatiere() . '.docx';

        return new StreamedResponse(
            static function() use ($templateProcessor) {
                $templateProcessor->saveAs('php://output');
            },
            200,
            [
                'Content-Description' => 'File Transfer',
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            ]
        );
    }

    private function prepareTexte($text)
    {
        $parseDown = new Parsedown();
        $phpWord = new PhpWord();
        $phpWord->addParagraphStyle('paragraph', ['alignment' => 'justify', 'spaceAfter' => Converter::cmToTwip(2)]);

        $section = $phpWord->addSection();
        $parseDown->setBreaksEnabled(true);
        $texte = $parseDown->text($text);

        Html::addHtml($section, $texte, false, true);

        return $section->getElements();
    }

    /**
     * @return StreamedResponse
     *
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function exportRessource(ApcRessource $apcRessource)
    {
        $templateProcessor = $this->genereWord($apcRessource);

        $filename = 'ressource_' . $apcRessource->getCodeMatiere() . '.docx';

        return new StreamedResponse(
            static function() use ($templateProcessor) {
                $templateProcessor->saveAs('php://output');
            },
            200,
            [
                'Content-Description' => 'File Transfer',
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            ]
        );
    }

    private function genereWord(ApcRessource $apcRessource)
    {
        $templateProcessor = new TemplateProcessor($this->dir . 'ressource.docx');

        $templateProcessor->setValue('nomressource',
            $apcRessource->getCodeMatiere() . ' - ' . $apcRessource->getLibelle());

        $competences = new TextRun();
        foreach ($apcRessource->getCompetences() as $competence) {
            $competences->addText('- ' . $competence->getLibelle());
            $competences->addTextBreak();
        }

        $acs = new TextRun();
        foreach ($apcRessource->getApcRessourceApprentissageCritiques() as $ac) {
            if (null !== $ac->getApprentissageCritique()) {
                $acs->addText('- ' . $ac->getApprentissageCritique()->getCode() . ' : ' . $ac->getApprentissageCritique()->getLibelle());
                $acs->addTextBreak();
            }
        }

        $saes = new TextRun();
        $listeSaes = $this->apcSaeRessourceRepository->findSaesByRessource($apcRessource);
        foreach ($listeSaes as $ac) {
            if (null !== $ac->getRessource()) {
                $saes->addText('- ' . $ac->getSae()->getCodeMatiere() . ' : ' . $ac->getSae()->getLibelle());
                $saes->addTextBreak();
            }
        }

        $ressources = new TextRun();
        foreach ($apcRessource->getRessourcesPreRequises() as $ac) {
            $ressources->addText('- ' . $ac->getCodeMatiere() . ' : ' . $ac->getLibelle());
            $ressources->addTextBreak();
        }

        $parcours = new TextRun();
        foreach ($apcRessource->getApcRessourceParcours() as $ac) {
            if (null !== $ac->getParcours()) {
                $parcours->addText('- ' . $ac->getParcours()->getLibelle());
                $parcours->addTextBreak();
            }
        }

        // get elements in section
        $containers = $this->prepareTexte($apcRessource->getDescription());
        $nbElements = count($containers);
        $templateProcessor->cloneBlock('descriptifblock', $nbElements, true, true);

        foreach ($containers as $i => $iValue) {
            if ($iValue !== null) {
                $templateProcessor->setComplexBlock('descriptif#' . ($i + 1), $iValue);
            }
        }

        if ($parcours !== null) {
            $templateProcessor->setComplexValue('parcours', $parcours);
        }

        $templateProcessor->setValue('heures',
            $apcRessource->getHeuresTotales() . 'h dont ' . $apcRessource->getTpPpn() . ' h TP');

        if ($saes !== null) {
            $templateProcessor->setComplexValue('sae', $saes);
        }
        if ($competences !== null) {
            $templateProcessor->setComplexValue('competences', $competences);
        }
        if ($acs !== null) {
            $templateProcessor->setComplexValue('apprentissages', $acs);
        }
        if ($ressources !== null) {
            $templateProcessor->setComplexValue('prerequis', $ressources);
        }

        $texte = '<div style="text-align:justify">' . $apcRessource->getMotsCles() . '</div>';
        $section = (new PhpWord())->addSection();
        Html::addHtml($section, $texte, false, true);
        if ($section->getElement(0) !== null) {
            $templateProcessor->setComplexBlock('motscles', $section->getElement(0));
        }

        $templateProcessor->setValue('semestre', $apcRessource->getSemestre()->getOrdreLmd());

        return $templateProcessor;
    }

    private function genereWordSae(ApcSae $apcSae)
    {
        $templateProcessor = new TemplateProcessor($this->dir . 'sae.docx');
        $templateProcessor->setValue('nomsae', $apcSae->getCodeMatiere() . ' - ' . $apcSae->getLibelle());

        $competences = new TextRun();
        foreach ($apcSae->getCompetences() as $competence) {
            $competences->addText('- ' . $competence->getLibelle());
            $competences->addTextBreak();
        }

        $acs = new TextRun();
        foreach ($apcSae->getApcSaeApprentissageCritiques() as $ac) {
            if (null !== $ac->getApprentissageCritique()) {
                $acs->addText('- ' . $ac->getApprentissageCritique()->getCode() . ' : ' . $ac->getApprentissageCritique()->getLibelle());
                $acs->addTextBreak();
            }
        }

        $ressources = new TextRun();
        $listeRessources = $this->apcSaeRessourceRepository->findRessourcesBySae($apcSae);
        foreach ($listeRessources as $ac) {
            if (null !== $ac->getRessource()) {
                $ressources->addText('- ' . $ac->getRessource()->getCodeMatiere() . ' : ' . $ac->getRessource()->getLibelle());
                $ressources->addTextBreak();
            }
        }

        $parcours = new TextRun();
        foreach ($apcSae->getApcSaeParcours() as $ac) {
            if (null !== $ac->getParcours()) {

                $parcours->addText('- ' . $ac->getParcours()->getLibelle());
                $parcours->addTextBreak();
            }
        }

        if ($parcours !== null) {
            $templateProcessor->setComplexValue('parcours', $parcours);
        }
        if ($competences !== null) {
            $templateProcessor->setComplexValue('competences', $competences);
        }

        // get elements in section
        $containers = $this->prepareTexte($apcSae->getObjectifs());
        $nbElements = count($containers);
        $templateProcessor->cloneBlock('objectifsblock', $nbElements, true, true);

        foreach ($containers as $i => $iValue) {
            if ($iValue !== null) {
                $templateProcessor->setComplexBlock('objectifs#' . ($i + 1), $iValue);
            }
        }

        // get elements in section
        $containers = $this->prepareTexte($apcSae->getDescription());
        $nbElements = count($containers);
        $templateProcessor->cloneBlock('descriptifblock', $nbElements, true, true);

        foreach ($containers as $i => $iValue) {
            if ($iValue !== null) {
                $templateProcessor->setComplexBlock('descriptif#' . ($i + 1), $iValue);
            }
        }

        if ($acs !== null) {
            $templateProcessor->setComplexValue('apprentissages', $acs);
        }
        if ($ressources !== null) {
            $templateProcessor->setComplexValue('ressources', $ressources);
        }
        $templateProcessor->setValue('semestre', $apcSae->getSemestre()->getOrdreLmd());

        return $templateProcessor;
    }

    public function exportAndSaveressource(ApcRessource $apcRessource, $dir)
    {
        $templateProcessor = $this->genereWord($apcRessource);

        $filename = 'ressource_' . $apcRessource->getCodeMatiere() . '.docx';

        $pathToSave = $dir . $filename;
        $templateProcessor->saveAs($pathToSave);

        return $pathToSave;
    }

    public function exportAndSaveSae(ApcSae $apcSae, $dir)
    {
        $templateProcessor = $this->genereWordSae($apcSae);

        $filename = 'sae_' . $apcSae->getCodeMatiere() . '.docx';

        $pathToSave = $dir . $filename;
        $templateProcessor->saveAs($pathToSave);

        return $pathToSave;
    }
}
