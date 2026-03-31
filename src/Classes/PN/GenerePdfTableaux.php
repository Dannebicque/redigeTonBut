<?php

namespace App\Classes\PN;

use App\Classes\Apc\TableauCroise;
use App\Classes\Tableau\Structure;
use App\Classes\Tableau\VolumesHoraires;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Entity\Version;
use App\Repository\SemestreRepository;
//use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
//use Knp\Snappy\Pdf;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GenerePdfTableaux
{

    /**
     * @var KernelInterface
     */
    public KernelInterface $kernel;
    private string $dir;

    private Departement $departement;

    public function __construct(
        KernelInterface                     $kernel,
        private readonly Environment        $twig,
        private readonly SemestreRepository $semestreRepository,
        private readonly TableauCroise      $tableauCroise,
//        private readonly Pdf                $knpSnappyPdf,
        private readonly Structure          $structure,
        protected VolumesHoraires           $volumesHoraires
    ) {
        $this->kernel = $kernel;
        $this->dir = $kernel->getProjectDir() . '/public/latex/';
    }

    public function genereTableauStructure(Version $version): void
    {
        $departement = $version->getDepartement();
        //type 2 et 1 (pour le type 3 1 par parcours...)
        if ($departement->getTypeStructure() === Departement::TYPE3) {
            $parcours = $version->getApcParcours();
            foreach ($parcours as $parcour) {
                $semestres = $this->semestreRepository->findByParcours($parcour);
                $this->genereStructureSemestres($semestres, $version, $parcour);
            }
        } else {
            $semestres = $version->getSemestres();
            $this->genereStructureSemestres($semestres, $version);
        }
    }

    public function genereTableauCroise(Version $version): void
    {
        $this->departement = $version->getDepartement();
        foreach ($version->getAnnees() as $annee) {
            $semestres = $this->semestreRepository->findBy(['annee' => $annee->getId()]);

            if ($annee->getOrdre() > 1 || $this->departement->getTypeStructure() === Departement::TYPE3) {
                $parcours = $version->getApcParcours();
            }

            foreach ($semestres as $semestre) {
                if ($annee->getOrdre() > 1 || $this->departement->getTypeStructure() === Departement::TYPE3) {
                    foreach ($parcours as $parcour) {
                        if ($this->departement->getTypeStructure() === Departement::TYPE3) {
                            $sems = $this->semestreRepository->findBy([
                                'annee' => $annee->getId(),
                                'apcParcours' => $parcour->getId()
                            ]);
                        } else {
                            $sems = $semestres;
                        }

                        $this->afficheParcours($parcour, $semestre, $sems);
                    }
                } else {
                    $this->affichePasParcours($semestre, $semestres);
                }
            }

        }
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function genereStructureSemestres(
        array $semestres,
        Version $version,
        ?ApcParcours $parcours = null
    ): void {
        throw new Exception('Fonctionnalité temporairement indisponible');
        $json = $this->structure->setSemestres($semestres)->setVersion($version)->getDataJson();

        $html = $this->twig->render('pdf/tableau-structure.html.twig', [
            'departement' => $version->getDepartement(),
            'version' => $version,
            'donnees' => $json,
            'parcours' => $parcours,
        ]);
        if (!$parcours instanceof ApcParcours) {
            $name = 'tableau-structure.pdf';
            //  $nameHtml = 'tableau-structure.html';
        } else {
            $name = 'tableau-structure-' . $parcours->getId() . '.pdf';
            //   $nameHtml = 'tableau-structure-' . $parcours->getId() . '.html';
        }

        ///  file_put_contents($this->dir . $departement->getNumeroAnnexe() . '/tableaux/' . $nameHtml, $html);


//        $output = new PdfResponse(
//            $this->knpSnappyPdf->getOutputFromHtml($html, [
//                'orientation' => 'Landscape'
//            ]),
//            $name
//        );
//
//        file_put_contents($this->dir . $version->getDepartement()->getNumeroAnnexe() . '/tableaux/' . $name, $output);
    }

    private function afficheParcours(ApcParcours $parcours, Semestre $semestre, array $semestres): void
    {
        $this->tableauCroise->getDatas($semestre, $parcours);
        $donnees = $this->volumesHoraires->setSemestres($semestres, $parcours)->getDataJson();
        $name = 'tableau-croise-' . $semestre->getid() . '-' . $parcours->getId() . '.pdf';
        $this->generePdfCroise($this->tableauCroise, $donnees, $name, $semestre, $parcours);
    }

    private function affichePasParcours(Semestre $semestre, array $semestres): void
    {
        $this->tableauCroise->getDatas($semestre);
        $donnees = $this->volumesHoraires->setSemestres($semestres)->getDataJson();
        $name = 'tableau-croise-' . $semestre->getid() . '.pdf';
        $this->generePdfCroise($this->tableauCroise, $donnees, $name, $semestre);
    }

    private function generePdfCroise(TableauCroise $tableauCroise, $donnees, string $name, Semestre $semestre, ?ApcParcours $parcours = null): void
    {
        throw new Exception('Fonctionnalité temporairement indisponible');

        $this->genereImage($tableauCroise->getRessources(), $tableauCroise->getSaes(), $this->departement);
        $html = $this->twig->render('pdf/tableau-croise.html.twig', [
            'linuxpath' => '/Users/davidannebicque/Sites/redigeTonBut/public/',
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
        //  file_put_contents($this->dir . $this->departement->getNumeroAnnexe() . '/tableaux/' . $name.'.html', $html);

//        $output = new PdfResponse(
//            $this->knpSnappyPdf->getOutputFromHtml($html, [
//                'enable-local-file-access' => true,
//                'zoom' => 0.75,
//            ]),
//            $name
//        );
//
//        file_put_contents($this->dir . $this->departement->getNumeroAnnexe() . '/tableaux/' . $name, $output);
    }

    private function genereImage($getRessources, $getSaes, Departement $departement): void
    {
        foreach ($getRessources as $ressource) {
            $texte = $ressource->getCodeMatiere() . ' ' . $ressource->getLibelle();
            $size = strlen($texte) < 30 ? 10 : 8;

            $texte = $this->adaptTexte($texte, $size);

            $response = new Response();
            $response->headers->set('Content-Type', 'image/png');
            $im = imagecreate(50, 200);
            $fond = imagecolorallocate($im, 255, 255, 255);
            $noir = imagecolorallocate($im, 0, 0, 0);
            imagefill($im, 0, 0, $fond);
            $font = $this->kernel->getProjectDir() . '/public/arial.ttf';
            imagettftext($im, $size, 90, 15, 190, $noir, $font, $texte);
            imagepng($im,
                $this->kernel->getProjectDir() . '/public/latex/' . $departement->getNumeroAnnexe() . '/tableaux/ressource_' . $ressource->getId() . '.png');
            imagedestroy($im);
        }

        foreach ($getSaes as $sae) {
            $texte = $sae->getCodeMatiere() . ' ' . $sae->getLibelle();
            $size = strlen($texte) < 30 ? 10 : 8;

            $texte = $this->adaptTexte($texte, $size);
            $response = new Response();
            $response->headers->set('Content-Type', 'image/png');
            $im = imagecreate(50, 200);
            $fond = imagecolorallocate($im, 173, 216, 230);
            $noir = imagecolorallocate($im, 0, 0, 0);
            imagefill($im, 0, 0, $fond);
            $font = $this->kernel->getProjectDir() . '/public/arial.ttf';
            imagettftext($im, 10, 90, 15, 190, $noir, $font, $texte);
            imagepng($im,
                $this->kernel->getProjectDir() . '/public/latex/' . $departement->getNumeroAnnexe() . '/tableaux/sae_' . $sae->getId() . '.png',
                2);
            imagedestroy($im);
        }
    }

    private function adaptTexte(string $texte, int $size): string
    {
        if ($size === 10) {
            return wordwrap($texte, 28, "\n", false);
        }

        return wordwrap($texte, 35, "\n", false);

    }

}
