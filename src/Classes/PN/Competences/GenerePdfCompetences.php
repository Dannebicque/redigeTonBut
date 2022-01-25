<?php

namespace App\Classes\PN\Competences;

use App\Classes\Apc\ApcStructure;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class GenerePdfCompetences
{

    private string $dir;
    private array $tParcours;
    private array $competencesParcours;
    private Departement $departement;
    private $competences;

    public function __construct(
        KernelInterface $kernel,
        private Environment $twig,
        private Pdf $knpSnappyPdf,
        private ApcStructure $apcStructure

    ) {
        $this->dir = $kernel->getProjectDir() . '/public/ref-competences/';
    }

    public function generePdfCompetencesParPage(Departement $departement): void
    {
        $this->departement = $departement;
        $this->getDataReferentiel();

        foreach ($this->departement->getApcParcours() as $parcours) {
            //pour chaque parcours
            // -> page de garde
            $this->generePageDeGarde($parcours);

            // ->page compétences / AC
            $this->generePageCompetencesComposantes($parcours);
            //-> Situation professionnelles
            $this->generePageSituationProfessionnelles($parcours);
            //-> Niveaux
            $this->generePageNiveaux($parcours);
            foreach ($this->competencesParcours[$parcours->getId()] as $competence) {
                //-> page compétence
                $this->generePageCompetence($parcours, $competence);
            }
        }
    }

    /**
     * @param \App\Entity\Departement $departement
     */
    public function generePdfCompetencesComplet(Departement $departement): void
    {
        $this->departement = $departement;
        $this->getDataReferentiel();

        $name = 'referentiel-competence-' . $departement->getSigle() . '.pdf';
        $html = $this->twig->render('competences/export-referentiel.html.twig', [
            'competencesParcours' => $this->competencesParcours,
            'departement' => $departement,
            'competences' => $this->competences,
            'parcours' => $departement->getApcParcours(),
            'parcoursNiveaux' => $this->tParcours,
        ]);

        $output = new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html, [
                'orientation' => 'Landscape'
            ]),
            $name
        );

        file_put_contents($this->dir . $departement->getNumeroAnnexe() . '/' . $name, $output);

    }

    private function getDataReferentiel()
    {
        $this->tParcours = $this->apcStructure->parcoursNiveaux($this->departement);
        $this->competences = $this->departement->getApcCompetences();
        $tComp = [];
        foreach ($this->competences as $comp) {
            $tComp[$comp->getId()] = $comp;
        }
        $this->competencesParcours = [];

        foreach ($this->tParcours as $key => $parc) {
            $this->competencesParcours[$key] = [];
            foreach ($parc as $k => $v) {
                $this->competencesParcours[$key][] = $tComp[$k];
            }
        }
    }

    private function generePagePdf(string $name, string $template, array $data)
    {
        $html = $this->twig->render('pdf/' . $template . '.html.twig', $data);

        $output = new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html, [
                'orientation' => 'Landscape'
            ]),
            $name
        );

        file_put_contents($this->dir . $this->departement->getNumeroAnnexe() . '/' . $name . '.pdf', $output);
    }

    private function generePageDeGarde(ApcParcours $parcours)
    {
        $this->generePagePdf('page_1_garde_' . $parcours->getId(), 'pageDeGardeParcours', [
            'parcours' => $parcours,
            'departement' => $this->departement
        ]);
    }

    private function generePageCompetencesComposantes(ApcParcours $parcours)
    {
        $this->generePagePdf('page_2_CompetencesComposantes_' . $parcours->getId(), 'pageCompetencesComposantes', [
            'parcours' => $parcours,
            'departement' => $this->departement,
            'competences' => $this->competencesParcours[$parcours->getId()]
        ]);
    }

    private function generePageSituationProfessionnelles(ApcParcours $parcours)
    {
        $this->generePagePdf('page_3_SituationProfessionnelles_' . $parcours->getId(), 'pageSituationProfessionnelles',
            [
                'parcours' => $parcours,
                'departement' => $this->departement,
                'competences' => $this->competencesParcours[$parcours->getId()]
            ]);
    }

    private function generePageNiveaux(ApcParcours $parcours)
    {
        $width = 100 / count($this->competencesParcours[$parcours->getId()]);

        $this->generePagePdf('page_4_PageNiveaux_' . $parcours->getId(), 'pagePageNiveaux', [
            'parcours' => $parcours,
            'departement' => $this->departement,
            'competences' => $this->competencesParcours[$parcours->getId()],
            'parcoursNiveaux' => $this->tParcours,
            'width' => $width - 2
        ]);
    }

    private function generePageCompetence(ApcParcours $parcours, mixed $competence)
    {
        $this->generePagePdf('page_5_Competence_' . $parcours->getId() . '_' . $competence->getId(), 'pageCompetence',
            [
                'competence' => $competence,
                'competencesParcours' => $this->competencesParcours,
                'departement' => $this->departement,
                'competences' => $this->competences,
                'parcours' => $parcours,
                'parcoursNiveaux' => $this->tParcours,
            ]);
    }
}
