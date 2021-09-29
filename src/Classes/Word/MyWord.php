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
use Parsedown;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class MyWord
{
    private string $dir;

    public function __construct(KernelInterface $kernel)
    {
        $this->dir = $kernel->getProjectDir() . '/public/word/';
        Settings::setOutputEscapingEnabled(true);
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

        $filename = 'sae_' . $apcSae->getCodeMatiere() . ' ' . $apcSae->getLibelle() . '.docx';

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
        $section = (new PhpWord())->addSection();
        $parseDown->setBreaksEnabled(true);
        $texte = $parseDown->text($text);
        $texte = '<div style="text-align:justify">'.$texte.'</div>';
        Html::addHtml($section, $texte, false, false);
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

        $filename = 'ressource_' . $apcRessource->getCodeMatiere() . ' ' . $apcRessource->getLibelle() . '.docx';

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
        foreach ($apcRessource->getApcSaeRessources() as $ac) {
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
            $templateProcessor->setComplexBlock('descriptif#' . ($i + 1), $iValue);
        }

        $templateProcessor->setComplexValue('parcours', $parcours);
        $templateProcessor->setValue('heures',
            $apcRessource->getHeuresTotales() . 'h dont ' . $apcRessource->getTpPpn() . ' h TP');
        $templateProcessor->setComplexValue('sae', $saes);
        $templateProcessor->setComplexValue('competences', $competences);
        $templateProcessor->setComplexValue('apprentissages', $acs);
        $templateProcessor->setComplexValue('prerequis', $ressources);

        $texte = '<div style="text-align:justify">'.$apcRessource->getMotsCles().'</div>';
        $section = (new PhpWord())->addSection();
        Html::addHtml($section, $texte, false, true);
        $templateProcessor->setComplexBlock('motscles', $section->getElement(0));

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
        foreach ($apcSae->getApcSaeRessources() as $ac) {
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

        $templateProcessor->setComplexValue('parcours', $parcours);
        $templateProcessor->setComplexValue('competences', $competences);

        // get elements in section
        $containers = $this->prepareTexte($apcSae->getObjectifs());
        $nbElements = count($containers);
        $templateProcessor->cloneBlock('objectifsblock', $nbElements, true, true);

        foreach ($containers as $i => $iValue) {
            $templateProcessor->setComplexBlock('objectifs#' . ($i + 1), $iValue);
        }

        // get elements in section
        $containers = $this->prepareTexte($apcSae->getDescription());
        $nbElements = count($containers);
        $templateProcessor->cloneBlock('descriptifblock', $nbElements, true, true);

        foreach ($containers as $i => $iValue) {
            $templateProcessor->setComplexBlock('descriptif#' . ($i + 1), $iValue);
        }

        $templateProcessor->setComplexValue('apprentissages', $acs);
        $templateProcessor->setComplexValue('ressources', $ressources);
        $templateProcessor->setValue('semestre', $apcSae->getSemestre()->getOrdreLmd());

        return $templateProcessor;
    }

    public function exportAndSaveressource(ApcRessource $apcRessource, $dir)
    {
        $templateProcessor = $this->genereWord($apcRessource);

        $filename = 'ressource_' . $apcRessource->getCodeMatiere() . ' ' . $apcRessource->getLibelle() . '.docx';

        $pathToSave = $dir.$filename;
        $templateProcessor->saveAs($pathToSave);
        return $pathToSave;
    }

    public function exportAndSaveSae(ApcSae $apcSae, $dir)
    {
        $templateProcessor = $this->genereWordSae($apcSae);

        $filename = 'sae_' . $apcSae->getCodeMatiere() . ' ' . $apcSae->getLibelle() . '.docx';

        $pathToSave = $dir.$filename;
        $templateProcessor->saveAs($pathToSave);
        return $pathToSave;
    }
}
