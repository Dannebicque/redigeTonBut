<?php

namespace App\Controller;

use App\Argumentaire\ArgumentaireDataProvider;
use App\Entity\Version;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArgumentaireController extends BaseController
{
    #[Route('/argumentaire/{version}', name: 'argumentaire_index')]
    public function index(
        ArgumentaireDataProvider $argumentaireDataProvider,
        Version $version
    ): Response
    {
        $data = $argumentaireDataProvider->getData($version);

        return $this->render('argumentaire/index.html.twig', [
            'version' => $version,
            'previousVersion' => $data['previousVersion'],
            'structureCurrent' => $data['structureCurrent'],
            'structurePrevious' => $data['structurePrevious'],
        ]);
    }
}
