<?php

namespace App\Classes\PN;

use App\Classes\Apc\TableauCroise;
use App\Classes\Tableau\Structure;
use App\Classes\Tableau\VolumesHoraires;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Semestre;
use App\Entity\Version;
use App\Pdf\Builder\TableauxSynthesePdfPayloadBuilder;
use App\Pdf\PdfManager;
use App\Pdf\PdfPayloadBuilderRegistry;
use App\Pdf\PdfSourceType;
use App\Repository\PdfDocumentRepository;
use App\Repository\SemestreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
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
        private readonly Structure          $structure,
        protected VolumesHoraires           $volumesHoraires,
        private readonly PdfManager         $pdfManager,
        private readonly PdfDocumentRepository $pdfDocumentRepository,
        private readonly PdfPayloadBuilderRegistry $pdfPayloadBuilderRegistry,
        private readonly HttpClientInterface $httpClient,
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
                $name = 'tableau-structure-' . $parcour->getId() . '.pdf';
                $this->genereStructureSemestres($semestres, $version, $name, $parcour);
            }
        } else {
            $name = 'tableau-structure.pdf';
            $this->genereStructureSemestres($version->getSemestres(), $version, $name);
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
        string $filename = 'tableau-structure.pdf',
        ?ApcParcours $parcours = null
    ): void {
        $this->structure->setSemestres($semestres)->setVersion($version)->getDataJson();
        $parameters = [
            'parcoursId' => $parcours?->getId(),
            'filename' => $filename,
        ];

        $request = $this->pdfPayloadBuilderRegistry->getBuilder(PdfSourceType::REFERENTIEL, TableauxSynthesePdfPayloadBuilder::DOCUMENT_KEY_STRUCTURE)
            ->build((string) $version->getId(), TableauxSynthesePdfPayloadBuilder::DOCUMENT_KEY_STRUCTURE, $parameters);

        $this->generatePdfFromHtml($version, $request->payload['html'] ?? '', $filename);
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
        $this->genereImage($tableauCroise->getRessources(), $tableauCroise->getSaes(), $this->departement);
        $version = $semestre->getVersion();
        if ($version === null) {
            return;
        }

        $parameters = [
            'semestreId' => $semestre->getId(),
            'parcoursId' => $parcours?->getId(),
            'filename' => $name,
        ];

        $request = $this->pdfPayloadBuilderRegistry->getBuilder(PdfSourceType::REFERENTIEL, TableauxSynthesePdfPayloadBuilder::DOCUMENT_KEY_CROISE)
            ->build((string) $version->getId(), TableauxSynthesePdfPayloadBuilder::DOCUMENT_KEY_CROISE, $parameters);

        $this->generatePdfFromHtml($version, $request->payload['html'] ?? '', $name);
    }

    private function generatePdfFromHtml(Version $version, string $html, string $filename): void
    {
        if (trim($html) === '') {
            return;
        }

        $departement = $version->getDepartement();
        if ($departement === null || $departement->getNumeroAnnexe() === null) {
            return;
        }

        $targetDir = $this->dir . $departement->getNumeroAnnexe() . '/tableaux';
        $pdfDir = $targetDir . '/pdf';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            return;
        }
        if (!is_dir($pdfDir) && !mkdir($pdfDir, 0777, true) && !is_dir($pdfDir)) {
            return;
        }

        $boundary = '----GotenbergBoundary' . bin2hex(random_bytes(16));
        $parts = [
            [
                'name' => 'files',
                'filename' => 'index.html',
                'contentType' => 'text/html',
                'contents' => $html,
            ],
            ['name' => 'landscape', 'contents' => 'true'],
            ['name' => 'printBackground', 'contents' => 'true'],
            ['name' => 'preferCSSPageSize', 'contents' => 'true'],
            ['name' => 'marginTop', 'contents' => '10mm'],
            ['name' => 'marginRight', 'contents' => '10mm'],
            ['name' => 'marginBottom', 'contents' => '10mm'],
            ['name' => 'marginLeft', 'contents' => '10mm'],
        ];

        $body = '';
        foreach ($parts as $part) {
            $body .= "--{$boundary}\r\n";
            $body .= 'Content-Disposition: form-data; name="' . $part['name'] . '"';
            if (isset($part['filename'])) {
                $body .= '; filename="' . $part['filename'] . '"';
            }
            $body .= "\r\n";

            if (isset($part['contentType'])) {
                $body .= 'Content-Type: ' . $part['contentType'] . "\r\n";
            }

            $body .= "\r\n";
            $body .= (string) $part['contents'];
            $body .= "\r\n";
        }
        $body .= "--{$boundary}--\r\n";

        $response = $this->httpClient->request('POST', 'http://gotenberg:3000/forms/chromium/convert/html', [
            'timeout' => 180,
            'headers' => [
                'Accept' => 'application/pdf',
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            ],
            'body' => $body,
        ]);

        if ($response->getStatusCode() !== 200) {
            $message = sprintf(
                'Erreur Gotenberg pour %s (status %d): %s',
                $filename,
                $response->getStatusCode(),
                $response->getContent(false)
            );

            throw new \RuntimeException($message);
        }

        file_put_contents($pdfDir . '/' . $filename, $response->getContent());
    }

    private function copyToLatexTableaux(Version $version, string $sourceFilePath, string $filename): void
    {
        $departement = $version->getDepartement();
        if ($departement !== null && $departement->getNumeroAnnexe() !== null && is_file($sourceFilePath)) {
            $targetDir = $this->dir . $departement->getNumeroAnnexe() . '/tableaux';
            $pdfDir = $targetDir . '/pdf';
            if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                return;
            }
            if (!is_dir($pdfDir) && !mkdir($pdfDir, 0777, true) && !is_dir($pdfDir)) {
                return;
            }
            @copy($sourceFilePath, $pdfDir . '/' . $filename);
        }
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
