<?php

namespace App\Controller\labset;

use App\Entity\QapesSae;
use App\Repository\QapesSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LabsetController extends AbstractController
{
    #[Route('/labset', name: 'app_labset')]
    #[IsGranted("ROLE_LABSET")]
    public function index(
        QapesSaeRepository $qapesSaeRepository
    ): Response
    {
        return $this->render('labset/index.html.twig', [
            'saes' => $qapesSaeRepository->findAll(),
        ]);
    }

    #[Route('/labset-public/', name: 'app_labset_public')]
    public function public(
        QapesSaeRepository $qapesSaeRepository
    ): Response
    {
        return $this->render('labset/index.html.twig', [
            'saes' => $qapesSaeRepository->findBy(['publiee' => true]),
        ]);
    }

    #[Route('/labset-public/{id}', name: 'app_qapes_sae_show', methods: ['GET'])]
    public function show(QapesSae $qapesSae): Response
    {
        return $this->render('labset/qapes_sae/show.html.twig', [
            'qapes' => $qapesSae,
        ]);
    }
}
