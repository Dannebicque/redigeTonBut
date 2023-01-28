<?php

namespace App\Controller\labset;

use App\Repository\QapesSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LabsetController extends AbstractController
{
    #[Route('/labset', name: 'app_labset')]
    public function index(
        QapesSaeRepository $qapesSaeRepository
    ): Response
    {
        return $this->render('labset/index.html.twig', [
            'saes' => $qapesSaeRepository->findAll(),
        ]);
    }
}
