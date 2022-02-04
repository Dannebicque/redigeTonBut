<?php

namespace App\Controller;

use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Repository\ApcRessourceParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\SemestreRepository;
use DateTime;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportPdfController extends BaseController
{
    #[Route('/export/pdf/parcours', name: 'export_pdf_parcours')]
    public function parcours(
        Pdf $knpSnappyPdf,
        ApcRessourceRepository $apcRessourceRepository
    ): Response {
        $nbParcours = $this->getDepartement()?->getApcParcours()->count();
        $semestres = $this->getDepartement()->getSemestres();

        $ressources = [];

        foreach ($semestres as $semestre) {
            $ressources[$semestre->getId()] = [];
            if ($semestre->getOrdreLmd() > 2) {
                $allRessources = $apcRessourceRepository->findBySemestre($semestre);
                foreach ($allRessources as $ressource) {
                    if ($ressource->getApcRessourceParcours()->count() > 0 && $ressource->getApcRessourceParcours()->count() < $nbParcours) {
                        $ressources[$semestre->getId()][] = $ressource;
                    }
                }
            }
        }

        $day = new DateTime('now');
        $name = 'referentiel-formation-' . $this->getDepartement()->getSigle() . '_parcours_' . $day->format('dmYHis') . '.pdf';
        $html = $this->renderView('formation/export-referentiel.html.twig', [
            'allParcours' => $this->getDepartement()->getApcParcours(),
            'departement' => $this->getDepartement(),
            'semestres' => $semestres,
            'ressources' => $ressources,
        ]);

        $header = $this->renderView('export_pdf/_header.html.twig',
            ['sigle' => $this->getDepartement()->getSigle()]);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html,
                [
                    'header-html' => $header,
                ]
            ),
            $name
        );
    }

    #[Route('/export/pdf/tronc-commun', name: 'export_pdf_tronc_commun')]
    public function troncCommun(
        Pdf $knpSnappyPdf,
        ApcRessourceRepository $apcRessourceRepository
    ): Response {
        $nbParcours = $this->getDepartement()?->getApcParcours()->count();
        $semestres = $this->getDepartement()->getSemestres();


        $ressources = [];

        foreach ($semestres as $semestre) {
            if ($semestre->getOrdreLmd() < 3) {
                $ressources[$semestre->getId()] = $apcRessourceRepository->findBySemestre($semestre);
            } else {
                $ressources[$semestre->getId()] = [];
                $allRessources = $apcRessourceRepository->findBySemestre($semestre);
                foreach ($allRessources as $ressource) {
                    if ($ressource->getApcRessourceParcours()->count() === $nbParcours || $ressource->getApcRessourceParcours()->count() === 0) {
                        $ressources[$semestre->getId()][] = $ressource;
                    }
                }
            }
        }

        $day = new DateTime('now');
        $name = 'referentiel-formation-' . $this->getDepartement()->getSigle() . '_tronc_commun_' . $day->format('dmYHis') . '.pdf';
        $html = $this->renderView('formation/export-referentiel.html.twig', [
            'allParcours' => $this->getDepartement()->getApcParcours(),
            'departement' => $this->getDepartement(),
            'semestres' => $semestres,
            'ressources' => $ressources,
        ]);

        $header = $this->renderView('export_pdf/_header.html.twig', ['sigle' => $this->getDepartement()->getSigle()]);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html,
                [
                    'header-html' => $header,
                ]
            ),
            $name
        );
    }

    #[Route('/export/pdf/{parcours}', name: 'export_pdf')]
    public function index(
        Pdf $knpSnappyPdf,
        SemestreRepository $semestreRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcSaeParcoursRepository $apcSaeParcoursRepository,
        ApcRessourceParcoursRepository $apcRessourceParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository,
        ApcParcours $parcours
    ): Response {
        if ($this->getDepartement()->getTypeStructure() === Departement::TYPE3) {
            $semestres = $semestreRepository->findByParcours($parcours);
        } else {
            $semestres = $this->getDepartement()->getSemestres();
        }

        $ressources = [];
        $saes = [];

        foreach ($semestres as $semestre) {
            if ($this->getDepartement()->getTypeStructure() !== Departement::TYPE3 && $semestre->getOrdreLmd() < 3) {
                $ressources[$semestre->getId()] = $apcRessourceRepository->findBySemestre($semestre);
                $saes[$semestre->getId()] = $apcSaeRepository->findBySemestre($semestre);
            } else {
                $ressources[$semestre->getId()] = $apcRessourceParcoursRepository->findBySemestre($semestre, $parcours);
                $saes[$semestre->getId()] = $apcSaeParcoursRepository->findBySemestre($semestre, $parcours);
            }
        }

        $day = new DateTime('now');
        $name = 'referentiel-formation-' . $this->getDepartement()->getSigle() . '_' . $parcours->getCode() . '_' . $day->format('dmYHis') . '.pdf';
        $html = $this->renderView('formation/export-referentiel.html.twig', [
            'allParcours' => $this->getDepartement()->getApcParcours(),
            'departement' => $this->getDepartement(),
            'semestres' => $semestres,
            'saes' => $saes,
            'ressources' => $ressources,
            'parcours' => $parcours,
        ]);

        $header = $this->renderView('export_pdf/_header.html.twig',
            ['sigle' => $this->getDepartement()->getSigle(), 'parcours' => $parcours->getLibelle()]);
        $footer = $this->renderView('export_pdf/_footer.html.twig');

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html,
                [
                    'header-html' => $header,
//                    'footer-html' => $footer,
                ]
            ),
            $name
        );
    }
}
