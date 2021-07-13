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
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Settings;
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
        $templateProcessor = new TemplateProcessor($this->dir . 'sae.docx');
        $templateProcessor->setValue('nomsae', $apcSae->getCodeMatiere() . ' - ' . $apcSae->getLibelle());
        $competences = '';
        foreach ($apcSae->getCompetences() as $competence) {
            $competences .= '- ' . $competence->getLibelle() . '</w:t><w:br/><w:t>';
        }

        $acs = '';
        foreach ($apcSae->getApcSaeApprentissageCritiques() as $ac) {
            if (null !== $ac->getApprentissageCritique()) {
                $acs .= '- ' . $ac->getApprentissageCritique()->getCode() . ' : ' . $ac->getApprentissageCritique()->getLibelle() . '</w:t><w:br/><w:t>';
            }
        }

        $ressources = '';
        foreach ($apcSae->getApcSaeRessources() as $ac) {
            if (null !== $ac->getRessource()) {
                $ressources .= '- ' . $ac->getRessource()->getCodeMatiere() . ' : ' . $ac->getRessource()->getLibelle() . '</w:t><w:br/><w:t>';
            }
        }

        $parcours = '';
        foreach ($apcSae->getApcSaeParcours() as $ac) {
            if (null !== $ac->getParcours()) {
                $parcours .= '- ' . $ac->getParcours()->getLibelle() . '</w:t><w:br/><w:t>';
            }
        }

        $templateProcessor->setValue('parcours', $parcours);
        $templateProcessor->setValue('competences', $competences);
        $templateProcessor->setValue('descriptif', $this->prepareTexte($apcSae->getDescription()));
        $templateProcessor->setValue('objectifs', $this->prepareTexte($apcSae->getObjectifs()));
        $templateProcessor->setValue('apprentissages', $acs);
        $templateProcessor->setValue('ressources', $ressources);
        $templateProcessor->setValue('semestre',  $apcSae->getSemestre()->getOrdreLmd());

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
        $text = nl2br(trim($text));
        $text = str_replace('<br />', '</w:t><w:br/><w:t>', $text);

        return $text;
    }

    /**
     * @return StreamedResponse
     *
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function exportRessource(ApcRessource $apcRessource)
    {
        $templateProcessor = new TemplateProcessor($this->dir . 'ressource.docx');

        $templateProcessor->setValue('nomressource',
            $apcRessource->getCodeMatiere() . ' - ' . $apcRessource->getLibelle());

        $competences = '';
        foreach ($apcRessource->getCompetences() as $competence) {
            $competences .= '- ' . $competence->getLibelle() . '</w:t><w:br/><w:t>';
        }

        $acs = '';
        foreach ($apcRessource->getApcRessourceApprentissageCritiques() as $ac) {
            if (null !== $ac->getApprentissageCritique()) {
                $acs .= '- ' . $ac->getApprentissageCritique()->getCode() . ' : ' . $ac->getApprentissageCritique()->getLibelle() . '</w:t><w:br/><w:t>';
            }
        }

        $saes = '';
        foreach ($apcRessource->getApcSaeRessources() as $ac) {
            if (null !== $ac->getRessource()) {
                $saes .= '- ' . $ac->getSae()->getCodeMatiere() . ' : ' . $ac->getSae()->getLibelle() . '</w:t><w:br/><w:t>';
            }
        }

        $ressources = '';
        foreach ($apcRessource->getRessourcesPreRequises() as $ac) {
                $ressources .= '- ' . $ac->getCodeMatiere() . ' : ' . $ac->getLibelle() . '</w:t><w:br/><w:t>';

        }

        $parcours = '';
        foreach ($apcRessource->getApcRessourceParcours() as $ac) {
            if (null !== $ac->getParcours()) {
                $parcours .= '- ' . $ac->getParcours()->getLibelle() . '</w:t><w:br/><w:t>';
            }
        }

        $templateProcessor->setValue('parcours', $parcours);
        $templateProcessor->setValue('descriptif', $this->prepareTexte($apcRessource->getDescription()));
        $templateProcessor->setValue('heures', $apcRessource->getHeuresTotales());
        $templateProcessor->setValue('heuresTP', $apcRessource->getTpPpn());
        $templateProcessor->setValue('sae', $saes);
        $templateProcessor->setValue('competences', $competences);
        $templateProcessor->setValue('apprentissages', $acs);
        $templateProcessor->setValue('prerequis', $ressources);
        $templateProcessor->setValue('motscles', $this->prepareTexte($apcRessource->getMotsCles()));
        $templateProcessor->setValue('semestre', $apcRessource->getSemestre()->getOrdreLmd());

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
}
