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
use Symfony\Component\HttpFoundation\Response;
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
        $this->kernel = $kernel;
        $this->dir = $kernel->getProjectDir() . '/public/latex/';
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
            $nameHtml = 'tableau-structure.html';
        } else {
            $name = 'tableau-structure-' . $parcours->getId() . '.pdf';
            $nameHtml = 'tableau-structure-' . $parcours->getId() . '.html';
        }

        file_put_contents($this->dir . $departement->getNumeroAnnexe() . '/tableaux/' . $nameHtml, $html);


        $output = new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html, [
                'orientation' => 'Landscape'
            ]),
            $name
        );

        file_put_contents($this->dir . $departement->getNumeroAnnexe() . '/tableaux/' . $name, $output);
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
        $this->genereImage($tableauCroise->getRessources(), $tableauCroise->getSaes(), $this->departement);
        $html = $this->twig->render('pdf/tableau-croise.html.twig', [
            'linuxpath' => '/Users/davidannebicque/htdocs/redigeTonBut/public/',
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
        file_put_contents($this->dir . $this->departement->getNumeroAnnexe() . '/tableaux/' . $name.'.html', $html);

        $output = new PdfResponse(
            $this->knpSnappyPdf->getOutputFromHtml($html, [
                'enable-local-file-access' => true,
            ]),
            $name
        );

        file_put_contents($this->dir . $this->departement->getNumeroAnnexe() . '/tableaux/' . $name, $output);
    }

    private function genereImage($getRessources, $getSaes, Departement $departement)
    {
        foreach ($getRessources as $ressource) {
            $texte = $ressource->getCodeMatiere() . ' ' . $ressource->getLibelle();
            $texte = $this->adaptTexte($texte);
            $response = new Response();
            $response->headers->set('Content-Type', 'image/png');
            $im = imagecreate(50, 200);
            $fond = imagecolorallocate($im, 255, 255, 255);
            $noir = imagecolorallocate($im, 0, 0, 0);
            imagefill($im, 0, 0, $fond);
            $font = $this->kernel->getProjectDir() . '/public/arial.ttf';
            imagettftext($im, 10, 90, 15, 190, $noir, $font, $texte);
            imagepng($im, $this->kernel->getProjectDir() . '/public/latex/'.$departement->getNumeroAnnexe().'/tableaux/ressource_' . $ressource->getId() . '.png');
            imagedestroy($im);
        }

        foreach ($getSaes as $sae) {
            $texte = $sae->getCodeMatiere() . ' ' . $sae->getLibelle();
            $texte = $this->adaptTexte($texte);
            $response = new Response();
            $response->headers->set('Content-Type', 'image/png');
            $im = imagecreate(50, 200);
            $fond = imagecolorallocate($im, 255, 255, 255);
            $noir = imagecolorallocate($im, 0, 0, 0);
            imagefill($im, 0, 0, $fond);
            $font = $this->kernel->getProjectDir() . '/public/arial.ttf';
            imagettftext($im, 10, 90, 15, 190, $noir, $font, $texte);
            imagepng($im, $this->kernel->getProjectDir() . '/public/latex/'.$departement->getNumeroAnnexe().'/tableaux/sae_' . $sae->getId() . '.png', 2);
            imagedestroy($im);
        }
    }

    private function adaptTexte(string $texte)
    {
        return wordwrap($texte, 30, "\n", false);
    }

}
