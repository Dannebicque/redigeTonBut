<?php

namespace App\Controller;

use App\Argumentaire\ArgumentaireDataProvider;
use App\Entity\Argumentaire;
use App\Entity\Version;
use App\Pdf\PdfManager;
use App\Pdf\PdfSourceType;
use App\Repository\ArgumentaireRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArgumentaireController extends BaseController
{
    public function __construct(
        private readonly PdfManager $pdfManager,
    ) {
    }

    #[Route('/argumentaire/{version}', name: 'argumentaire_index')]
    public function index(
        Request $request,
        ArgumentaireDataProvider $argumentaireDataProvider,
        ArgumentaireRepository $argumentaireRepository,
        Version $version
    ): Response
    {
        if (!$this->isGranted('FORMATION_EDIT', $version)) {
            throw $this->createAccessDeniedException();
        }


        $data = $argumentaireDataProvider->getData($version);
        $argumentaire = $this->findOrCreateArgumentaire($version, $argumentaireRepository);
        $sourceId = (string) $version->getId();
        $statusMap = $this->pdfManager->getDisplayStatusesForSources(PdfSourceType::ARGUMENTAIRE, [$sourceId]);
        $pdfStatusData = $statusMap[$sourceId] ?? ['status' => PdfManager::DISPLAY_STATUS_ABSENT, 'errorMessage' => null];

        if ($request->isMethod('POST')) {
            $payload = $this->normalizePayload($request->request->all());
            $argumentaire->setPayload($payload)->touch();
            $this->entityManager->persist($argumentaire);
            $this->entityManager->flush();

            $this->addFlashBag('success', 'Argumentaire sauvegarde.');

            return $this->redirectToRoute('argumentaire_index', ['version' => $version->getId()]);
        }


        return $this->render('argumentaire/index.html.twig', [
            'version' => $version,
            'previousVersion' => $data['previousVersion'],
            'structureCurrent' => $data['structureCurrent'],
            'structurePrevious' => $data['structurePrevious'],
            'argumentairePayload' => $argumentaire->getPayload(),
            'argumentaireUpdatedAt' => $argumentaire->getUpdatedAt(),
            'pdfStatusData' => $pdfStatusData,
        ]);
    }

    #[Route('/argumentaire/{version}/autosave', name: 'argumentaire_autosave', methods: ['POST'])]
    public function autosave(
        Request $request,
        ArgumentaireRepository $argumentaireRepository,
        Version $version
    ): JsonResponse
    {
        if (!$this->isGranted('FORMATION_EDIT', $version)) {
            throw $this->createAccessDeniedException();
        }

        $argumentaire = $this->findOrCreateArgumentaire($version, $argumentaireRepository);

        $content = json_decode($request->getContent(), true);
        if (!is_array($content)) {
            return $this->json(['success' => false, 'message' => 'Payload invalide.'], 400);
        }

        $currentPayload = $argumentaire->getPayload();

        if (($content['mode'] ?? null) === 'full') {
            $payload = $this->normalizePayload(is_array($content['payload'] ?? null) ? $content['payload'] : []);
            $argumentaire->setPayload($payload)->touch();
        } else {
            $field = $content['field'] ?? null;
            if (!is_string($field) || $field === '') {
                return $this->json(['success' => false, 'message' => 'Champ manquant.'], 400);
            }

            $value = $content['value'] ?? '';
            $normalizedValue = is_scalar($value) ? trim((string) $value) : '';

            if ($normalizedValue === '') {
                unset($currentPayload[$field]);
            } else {
                $currentPayload[$field] = $normalizedValue;
            }

            $argumentaire->setPayload($currentPayload)->touch();
        }

        $this->entityManager->persist($argumentaire);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'updatedAt' => $argumentaire->getUpdatedAt()?->format(DATE_ATOM),
        ]);
    }

    private function findOrCreateArgumentaire(Version $version, ArgumentaireRepository $argumentaireRepository): Argumentaire
    {
        $argumentaire = $argumentaireRepository->findOneByVersion($version);

        if ($argumentaire instanceof Argumentaire) {
            return $argumentaire;
        }

        $argumentaire = new Argumentaire();
        $argumentaire->setVersion($version);

        return $argumentaire;
    }

    /**
     * @param array<string, mixed> $rawPayload
     *
     * @return array<string, string>
     */
    private function normalizePayload(array $rawPayload): array
    {
        $payload = [];

        foreach ($rawPayload as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            if (!is_scalar($value) && $value !== null) {
                continue;
            }

            $normalizedValue = trim((string) $value);
            if ($normalizedValue === '') {
                continue;
            }

            $payload[$key] = $normalizedValue;
        }

        return $payload;
    }
}
