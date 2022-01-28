<?php

namespace App\Classes\PN;

use App\Classes\Apc\TableauCroise;
use App\Classes\Tableau\Structure;
use App\Classes\Tableau\VolumesHoraires;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Repository\SemestreRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class GenerePdfTableaux
{

    private string $dir;
    private Departement $departement;

    public function __construct(
        KernelInterface $kernel,
        private Environment $twig,
        private SemestreRepository $semestreRepository,
        private TableauCroise $tableauCroise,
        private Pdf $knpSnappyPdf,
        private Structure $structure,
        protected VolumesHoraires $volumesHoraires
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/tableaux/';
    }

    public function genereTableauStructure(Departement $departement): void
    {
        //type 2 et 1 (pour le type 3 1 par parcours...)
        if ($departement->getTypeStructure() === Departement::TYPE3) {
            $parcours = $departement->getApcParcours();
            foreach ($parcours as $parcour) {
                $semestres = $this->semestreRepository->findByParcours($parcour);
                $this->genereStructureSemestres($semestres, $departement, $parcour);
            }
        } else {
            $semestres = $departement->getSemestres();
            $this->genereStructureSemestres($semestres, $departement);
        }
    }

    public function genereTableauCroise(Departement $departement): void
    {
        $this->departement = $departement;
        foreach ($departement->getAnnees() as $annee) {

            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId()]);
            if ($annee->getOrdre() > 1 || $departement->getTypeStructure() === Departement::TYPE3) {
                $parcours = $departement->getApcParcours();
            }

            foreach ($semestres as $semestre) {
                if ($annee->getOrdre() > 1 || $departement->getTypeStructure() === Departement::TYPE3) {
                    foreach ($parcours as $parcour) {
                        $this->afficheParcours($parcour, $semestre, $semestres);
                    }
                } else {
                    $this->affichePasParcours($semestre, $semestres);
                }
            }

        }
    }

    /**
     * @param array                        $semestres
     * @param \App\Entity\Departement      $departement
     * @param \App\Entity\ApcParcours|null $parcours
     *
     * @return void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function genereStructureSemestres(
        array $semestres,
        Departement $departement,
        ?ApcParcours $parcours = null
    ): void {
        $json = $this->structure->setSemestres($semestres)->setDepartement($departement)->getDataJson();

        $html = $this->twig->render('pdf/tableau-structure.html.twig', [
            'departement' => $departement,
            'donnees' => $json,
            'parcours' => $parcours,
        ]);
        if ($parcours === null) {
            $name = 'tableau-structure.pdf';
        } else {
            $name = 'tableau-structure-' . $parcours->getId() . '.pdf';
        }

        $output = new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html, [
                'orientation' => 'Landscape'
            ]),
            $name
        );

        file_put_contents($this->dir . $departement->getNumeroAnnexe() . '/' . $name, $output);
    }

    private function afficheParcours(ApcParcours $parcours, Semestre $semestre, array $semestres)
    {
        $this->tableauCroise->getDatas($semestre, $parcours);
        $donnees = $this->volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();
        $name = 'tableau-croise-' . $semestre->getid() . '-' . $parcours->getId() . '.pdf';
        $this->generePdfCroise($this->tableauCroise, $donnees, $name, $semestre, $parcours);
    }

    private function affichePasParcours(Semestre $semestre, array $semestres)
    {
        $this->tableauCroise->getDatas($semestre);
        $donnees = $this->volumesHoraires->setSemestres($semestres)->getDataJson();
        $name = 'tableau-croise-' . $semestre->getid() . '.pdf';
        $this->generePdfCroise($this->tableauCroise, $donnees, $name, $semestre);
    }

    private function generePdfCroise($tableauCroise, $donnees, $name, Semestre $semestre, ?ApcParcours $parcours = null)
    {
        $html = $this->twig->render('pdf/tableau-croise.html.twig', [
            'departement' => $this->departement,
            'donnees' => $donnees,
            'semestre' => $semestre,
            'niveaux' => $tableauCroise->getNiveaux(),
            'saes' => $tableauCroise->getSaes(),
            'ressources' => $tableauCroise->getRessources(),
            'tab' => $tableauCroise->getTab(),
            'coefficients' => $tableauCroise->getCoefficients(),
            'parcours' => $parcours,
        ]);

        $output = new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html, [
            ]),
            $name
        );

        file_put_contents($this->dir . $this->departement->getNumeroAnnexe() . '/' . $name, $output);
    }

}
