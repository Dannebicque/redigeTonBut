<?php

namespace App\Controller;

use App\Classes\DataUserSession;
use App\Entity\Departement;
use App\Entity\Version;
use App\Pdf\Builder\ReferentielPdfPayloadBuilder;
use App\Pdf\PdfManager;
use App\Pdf\PdfSourceType;
use App\Repository\DepartementRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    public function __construct(
        private readonly PdfManager $pdfManager,
        private readonly DataUserSession $dataUserSession,
    ) {
    }

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        $version = $this->dataUserSession->getVersion();

        return $this->render('default/index.html.twig', [
            'pdfReferentielStatuses' => $this->buildReferentielPdfStatuses($version),
        ]);
    }

    /**
     * @return array{complete: array{status: string, errorMessage: ?string, lastGeneratedAt: ?\DateTimeImmutable}}
     */
    private function buildReferentielPdfStatuses(?Version $version): array
    {
        $defaultStatus = [
            'status' => PdfManager::DISPLAY_STATUS_ABSENT,
            'errorMessage' => null,
            'lastGeneratedAt' => null,
        ];

        if ($version === null || $version->getId() === null) {
            return [
                'complete' => $defaultStatus,
            ];
        }

        $sourceId = (string) $version->getId();

        $statuses = $this->pdfManager->getDisplayStatusesForSources(
            PdfSourceType::REFERENTIEL,
            [$sourceId],
            ReferentielPdfPayloadBuilder::DOCUMENT_KEY_COMPLETE,
        );

        return [
            'complete' => $statuses[$sourceId] ?? $defaultStatus,
        ];
    }

    #[Route('/direct/{departement}', name: 'homepage_direct_specialite')]
    public function directSpecialite(
        DepartementRepository $departementRepository,
        RequestStack          $requestStack,
        ?string $departement = null): Response
    {
        //todo: gérer pour avoir la version aussi
        if ($departement !== null) {
            $dept = $departementRepository->findOneBy(['sigle' => $departement]);
            if ($dept) {
                $requestStack->getSession()->set('departement', $dept->getId());
            }
        }

        return $this->redirectToRoute('homepage_specialite');
    }

    #[Route('/specialite', name: 'homepage_specialite')]
    public function indexSpecialite(): Response
    {
        return $this->render('default/index-specialite.html.twig', [
        ]);
    }

    #[Route('/change-specialite/{departement}', name: 'change_specialite')]
    public function changeSpecialite(RequestStack $requestStack, Departement $departement): Response
    {
        if ($this->isGranted('ROLE_GT') || $this->isGranted('ROLE_EDITEUR') || $this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_IUT') || $this->isGranted('ROLE_CPN_LECTEUR')) {

            $requestStack->getSession()->set('departement', $departement->getId());

            if ($requestStack->getCurrentRequest()->query->has('redirect')) {
                return $this->redirectToRoute($requestStack->getCurrentRequest()->query->get('redirect'));
            }

            if ($this->isGranted('ROLE_IUT')) {
                return $this->redirectToRoute('homepage_specialite');
            }

            return $this->redirectToRoute('homepage');
        }

        throw new Exception('Fonctionnalité interdite au regard de vos droits.');
    }

    #[Route('/change-version/{annee}', name: 'change_version')]
    public function changeVersion(RequestStack $requestStack, int $annee): Response
    {
        if ($annee === 2021 || $annee === 2027) {
            if ($this->isGranted('ROLE_GT') || $this->isGranted('ROLE_CPN_LECTEUR') || $this->isGranted('ROLE_LECTEUR') || $this->isGranted('ROLE_EDITEUR') || $this->isGranted('ROLE_CPN') || $this->isGranted('ROLE_PACD')) {

                $requestStack->getSession()->set('versionPn', $annee);


                return $this->redirectToRoute('homepage');
            }

            throw new \RuntimeException('Fonctionnalité interdite au regard de vos droits.');
        }

        throw new Exception('Année de version inexistante.');
    }
}
