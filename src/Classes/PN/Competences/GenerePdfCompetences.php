<?php

namespace App\Classes\PN\Competences;

use App\Classes\Apc\ApcStructure;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Version;
use App\Pdf\Builder\CompetencesReferentielPdfPayloadBuilder;
use App\Pdf\PdfPayloadBuilderRegistry;
use App\Pdf\PdfSourceType;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

class GenerePdfCompetences
{
    private string $dir;

    private array $tParcours = [];

    private array $competencesParcours = [];

    private Version $version;

    private Departement $departement;

    private ?Collection $competences = null;

    private Filesystem $filesystem;

    public function __construct(
        KernelInterface $kernel,
        private readonly Environment $twig,
        private readonly ApcStructure $apcStructure,
        private readonly PdfPayloadBuilderRegistry $pdfPayloadBuilderRegistry,
        private readonly HttpClientInterface $httpClient,
    ) {
        $this->dir = $kernel->getProjectDir() . '/public/latex/';
        $this->filesystem = new Filesystem();
    }

    public function generePdfCompetencesParPage(Version $version): void
    {
        $this->departement = $version->getDepartement();
        $this->version = $version;

        $this->filesystem->mkdir($this->dir . $this->departement->getNumeroAnnexe() . '/ref-competences/', 0777);
        $this->getDataReferentiel();

        foreach ($this->version->getApcParcours() as $parcours) {
            $this->generePageDeGarde($parcours);
            $this->generePageCompetencesComposantes($parcours);
            $this->generePageSituationProfessionnelles($parcours);
            $this->generePageNiveaux($parcours);

            foreach ($this->competencesParcours[$parcours->getId()] as $competence) {
                $this->generePageCompetence($parcours, $competence);
            }
        }
    }

    public function generePdfCompetencesComplet(Version $version): void
    {
        $departement = $version->getDepartement();
        $this->filesystem->mkdir($this->dir . $departement->getNumeroAnnexe() . '/ref-competences/', 0777);

        $request = $this->pdfPayloadBuilderRegistry
            ->getBuilder(PdfSourceType::REFERENTIEL, CompetencesReferentielPdfPayloadBuilder::DOCUMENT_KEY)
            ->build((string) $version->getId(), CompetencesReferentielPdfPayloadBuilder::DOCUMENT_KEY);

        $this->writePdf(
            $departement,
            $request->payload['html'] ?? '',
            sprintf('referentiel-competence-%s.pdf', $departement->getSigle())
        );
    }

    private function getDataReferentiel(): void
    {
        $this->tParcours = $this->apcStructure->parcoursNiveaux($this->version);
        $this->competences = $this->version->getApcCompetences();
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

    private function generePagePdf(string $name, string $template, array $data): void
    {
        $html = $this->twig->render('pdf/' . $template . '.html.twig', $data);
        $this->writePdf($this->departement, $html, $name . '.pdf');
    }

    private function writePdf(Departement $departement, string $html, string $filename): void
    {
        if (trim($html) === '') {
            return;
        }

        $targetDir = $this->dir . $departement->getNumeroAnnexe() . '/ref-competences';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
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
            throw new \RuntimeException(sprintf(
                'Erreur Gotenberg pour %s (status %d): %s',
                $filename,
                $response->getStatusCode(),
                $response->getContent(false)
            ));
        }

        file_put_contents($targetDir . '/' . $filename, $response->getContent());
    }

    private function generePageDeGarde(ApcParcours $parcours): void
    {
        $this->generePagePdf('page_1_garde_' . $parcours->getId(), 'pageDeGardeParcours', [
            'parcours' => $parcours,
            'version' => $this->version,
            'departement' => $this->departement,
        ]);
    }

    private function generePageCompetencesComposantes(ApcParcours $parcours): void
    {
        $this->generePagePdf('page_2_CompetencesComposantes_' . $parcours->getId(), 'pageCompetencesComposantes', [
            'parcours' => $parcours,
            'departement' => $this->departement,
            'competences' => $this->competencesParcours[$parcours->getId()],
        ]);
    }

    private function generePageSituationProfessionnelles(ApcParcours $parcours): void
    {
        $this->generePagePdf('page_3_SituationProfessionnelles_' . $parcours->getId(), 'pageSituationProfessionnelles', [
            'parcours' => $parcours,
            'departement' => $this->departement,
            'competences' => $this->competencesParcours[$parcours->getId()],
        ]);
    }

    private function generePageNiveaux(ApcParcours $parcours): void
    {
        $width = 100 / count($this->competencesParcours[$parcours->getId()]);

        $this->generePagePdf('page_4_PageNiveaux_' . $parcours->getId(), 'pagePageNiveaux', [
            'parcours' => $parcours,
            'departement' => $this->departement,
            'competences' => $this->competencesParcours[$parcours->getId()],
            'parcoursNiveaux' => $this->tParcours,
            'width' => $width - 2,
        ]);
    }

    private function generePageCompetence(ApcParcours $parcours, mixed $competence): void
    {
        $this->generePagePdf('page_5_Competence_' . $parcours->getId() . '_' . $competence->getId(), 'pageCompetence', [
            'competence' => $competence,
            'competencesParcours' => $this->competencesParcours,
            'departement' => $this->departement,
            'competences' => $this->competences,
            'parcours' => $parcours,
            'parcoursNiveaux' => $this->tParcours,
        ]);
    }
}
