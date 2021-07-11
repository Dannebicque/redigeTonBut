<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Form\DepartementType;
use App\Repository\DepartementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/administration/specialite', name: 'administration_departement_')]
class DepartementController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        DepartementRepository $departementRepository,
        UserRepository $userRepository
    ): Response {
        return $this->render('departement/index.html.twig', [
            'departements' => $departementRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(
        Departement $departement
    ): Response {
        return $this->render('departement/show.html.twig', [
            'departement' => $departement,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Departement $departement
    ): Response {
        $form = $this->createForm(DepartementType::class, $departement,
        ['droit' => $this->isGranted('ROLE_GT')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('administration_departement_index');
        }

        return $this->render('departement/edit.html.twig', [
            'departement' => $departement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{departement}/update/ajax', name: 'update_ajax', methods: ['POST'], options: ["expose" => true])]
    public function updateDepartement(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        Request $request,
        Departement $departement
    ) {
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        switch ($parametersAsArray['field']) {
            case 'cpn':
                $us = $departement->getCpn();
                if ($us !== null) {
                    $us->setRoles(['ROLE_LECTEUR']);
                }

                if ($parametersAsArray['value'] !== '') {
                    $user = $userRepository->find($parametersAsArray['value']);
                    if ($user !== null) {
                        $user->setRoles(['ROLE_CPN']);
                    }
                }
                break;
            case 'pacd':
                $us = $departement->getPacd();
                if ($us !== null) {
                    $us->setRoles(['ROLE_LECTEUR']);
                }

                if ($parametersAsArray['value'] !== '') {
                    $user = $userRepository->find($parametersAsArray['value']);
                    if ($user !== null) {
                        $user->setRoles(['ROLE_PACD']);
                    }
                }
                break;
        }

        $entityManager->flush();

        return $this->json(true);
    }
}
