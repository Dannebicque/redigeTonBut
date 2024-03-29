<?php

namespace App\Controller;

use App\Entity\Constantes;
use App\Entity\Departement;
use App\Form\DepartementType;
use App\Repository\DepartementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'administration_departement_')]
class DepartementController extends AbstractController
{
    #[Route('/administration/specialite/', name: 'index', methods: ['GET'])]
    public function index(
        DepartementRepository $departementRepository,
        UserRepository $userRepository
    ): Response {
        return $this->render('departement/index.html.twig', [
            'departements' => $departementRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/administration/specialite/{id}', name: 'show', methods: ['GET'])]
    public function show(
        Departement $departement
    ): Response {
        return $this->render('departement/show.html.twig', [
            'departement' => $departement,
        ]);
    }

    #[Route('/specialite/{id}/lecteur', name: 'lecteur_show', methods: ['GET'])]
    public function showLecteur(
        Departement $departement
    ): Response {
        return $this->render('departement/showLecteur.html.twig', [
            'departement' => $departement,
        ]);
    }

    #[Route('/administration/specialite/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Departement $departement
    ): Response {
        $form = $this->createForm(DepartementType::class, $departement,
        ['droit' => $this->isGranted('ROLE_GT')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                Constantes::FLASHBAG_SUCCESS,
                'Spécialité/Parcours mis à jour avec succès.'
            );
        }

        return $this->render('departement/edit.html.twig', [
            'departement' => $departement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/specialite/{departement}/update/ajax', name: 'update_ajax', methods: ['POST'], options: ["expose" => true])]
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
