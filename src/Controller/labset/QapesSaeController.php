<?php

namespace App\Controller\labset;

use App\Entity\QapesSae;
use App\Entity\QapesSaeCritereReponse;
use App\Form\QapesSaePart1Type;
use App\Form\QapesSaePart2Type;
use App\Form\QapesSaePart3Type;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\DepartementRepository;
use App\Repository\IutSiteParcoursRepository;
use App\Repository\IutSiteRepository;
use App\Repository\QapesCritereRepository;
use App\Repository\QapesSaeCritereReponseRepository;
use App\Repository\QapesSaeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/labset/qapes/sae')]
class QapesSaeController extends AbstractController
{

    #[Route('/api', name: 'app_qapes_sae_api', methods: ['GET', 'POST'])]
    public function api(
        ApcSaeRepository $apcSaeRepository,
        DepartementRepository $departementRepository,
        IutSiteParcoursRepository $iutSiteParcoursRepository,
        IutSiteRepository $iutSiteRepository,
        Request $request
    ) {
        $action = $request->query->get('action');
        switch ($action) {
            case 'siteIut':
                $iut = $request->query->get('iut');
                $siteiut = $iutSiteRepository->findBy(['iut' => $iut]);
                $siteiutArray = [];
                foreach ($siteiut as $site) {
                    $siteiutArray[] = [
                        'id' => $site->getId(),
                        'libelle' => $site->getLibelle(),
                    ];
                }

                return new JsonResponse($siteiutArray);
            case 'parcours':
                $site = $request->query->get('siteIut');
                $parcours = $iutSiteParcoursRepository->findBy(['site' => $site]);
                $parcoursArray = [];
                foreach ($parcours as $parcour) {
                    $parcoursArray[] = [
                        'id' => $parcour->getParcours()?->getId(),
                        'libelle' => $parcour->getParcours()?->getLibelle(). ' ('.$parcour->getParcours()?->getDepartement()?->getSigle().')'
                    ];
                }

                return new JsonResponse($parcoursArray);
            case 'specialite':
                $iut = $request->query->get('siteIut');
                $siteiut = $departementRepository->findBySiteIut($iut);
                $siteiutArray = [];
                foreach ($siteiut as $site) {
                    $siteiutArray[] = [
                        'id' => $site->getId(),
                        'libelle' => $site->getLibelle(),
                    ];
                }

                return new JsonResponse($siteiutArray);

            case 'saeFromParcours':
                $iut = $request->query->get('parcours');
                $siteiut = $apcSaeRepository->findByParcours($iut);
                $siteiutArray = [];
                foreach ($siteiut as $site) {
                    $siteiutArray[] = [
                        'id' => $site->getId(),
                        'libelle' => $site->getDisplay(),
                    ];
                }

                return new JsonResponse($siteiutArray);

            case 'saeFromSpecialite':
                $iut = $request->query->get('specialite');
                $departement = $departementRepository->find($iut);
                $siteiut = $apcSaeRepository->findByDepartement($departement);
                $siteiutArray = [];
                foreach ($siteiut as $site) {
                    $siteiutArray[] = [
                        'id' => $site->getId(),
                        'libelle' => $site->getDisplay(),
                    ];
                }

                return new JsonResponse($siteiutArray);
        }

    }

//    #[Route('/api/{qapes}', name: 'app_qapes_sae_api_step3', methods: ['GET', 'POST'])]
//    public function apiStep3(
//        QapesCritereRepository $qapesCriteresEvaluationRepository,
//        QapesSaeCritereReponseRepository $qapesSaeCritereReponseRepository,
//        QapesSae $qapes,
//        Request $request
//    ) {
//        $action = $request->query->get('action');
//        switch ($action) {
//            case 'afficheFormCritere':
//                $critere = $qapesCriteresEvaluationRepository->find($request->query->get('critereId'));
//
//                return $this->render('labset/qapes_sae/_form_critere.html.twig', [
//                    'critere' => $critere,
//                    'listeChoix' => explode(',', $critere->getValeurs()),
//                ]);
//            case 'listeCritere':
//                $criteres = $qapesSaeCritereReponseRepository->findBy(['qapes_sae' => $qapes]);
//
//                return $this->render('labset/qapes_sae/_liste_criteres.html.twig', [
//                    'criteres' => $criteres,
//                ]);
//        }
//
//    }


//    #[Route('/post-3/{qapes}', name: 'app_qapes_sae_post_step3', methods: ['POST'])]
//    public function postStep3(
//        EntityManagerInterface $entityManager,
//        QapesCritereRepository $qapesCriteresEvaluationRepository,
//        QapesSaeCritereReponseRepository $qapesSaeCritereReponseRepository,
//        QapesSae $qapes,
//        Request $request
//    ) {
//        $data = json_decode($request->getContent(), true);
//
//        $qc = new QapesSaeCritereReponse();
//        $qc->setQapesSae($qapes);
//        $qc->setQapesCritere($qapesCriteresEvaluationRepository->find($data['critereId']));
//        $qc->setReponse($data['reponse']);
//        $qc->setCommentaireRepose($data['commentaire']);
//        $entityManager->persist($qc);
//        $entityManager->flush();
//
//        return $this->json(true);
//
//    }


    #[Route('/new', name: 'app_qapes_sae_new', methods: ['GET', 'POST'])]
    public function new(
        IutSiteRepository $iutSiteRepository,
        ApcParcoursRepository $parcoursRepository,
        DepartementRepository $departementRepository,
        ApcSaeRepository $apcSaeRepository,
        Request $request, QapesSaeRepository $qapesSaeRepository): Response
    {
        $qapesSae = new QapesSae($this->getUser());
        $form = $this->createForm(QapesSaePart1Type::class, $qapesSae);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['qapes_sae_part1'];
            //iutSite
            $iutSite = $iutSiteRepository->find($data['iutSite'] );
            $qapesSae->setIutSite($iutSite);

            //parcours
            if ($data['parcours']  !== '') {
                $parcours = $parcoursRepository->find($data['parcours'] );
                $qapesSae->setParcours($parcours);
                $qapesSae->setSpecialite($parcours->getDepartement());
            }

            //specialite
            if ($data['specialite']  !== '') {
                $specialite = $departementRepository->find($data['specialite'] );
                $qapesSae->setSpecialite($specialite);
            }

            if ($data['sae'] !== '') {
                $sae = $apcSaeRepository->find($data['sae']);
                $qapesSae->setSae($sae);
            }

            $qapesSaeRepository->add($qapesSae);

            return $this->redirectToRoute('app_qapes_sae_new_etape_2', [
                'qapesSae' => $qapesSae->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('labset/qapes_sae/new.html.twig', [
            'qapes_sae' => $qapesSae,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/etape-2/{qapesSae}', name: 'app_qapes_sae_new_etape_2', methods: ['GET', 'POST'])]
    public function etape2(Request $request, QapesSaeRepository $qapesSaeRepository, QapesSae $qapesSae): Response
    {
        $form = $this->createForm(QapesSaePart2Type::class, $qapesSae);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $qapesSaeRepository->add($qapesSae);

            return $this->redirectToRoute('app_qapes_sae_new_etape_3', [
                'qapesSae' => $qapesSae->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('labset/qapes_sae/new_step2.html.twig', [
            'qapes' => $qapesSae,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/etape-3/{qapesSae}', name: 'app_qapes_sae_new_etape_3', methods: ['GET', 'POST'])]
    public function etape3(
        QapesCritereRepository $qapesCritereRepository,
        Request $request,
        QapesSaeRepository $qapesSaeRepository,
        QapesSae $qapesSae
    ): Response {

        $criteres = $qapesCritereRepository->findAll();
        $reponsesCriteres = $qapesSae->getQapesSaeCritereReponse();
        $t = [];
        foreach ($reponsesCriteres as $reponseCritere) {
            $t[$reponseCritere->getCritere()->getId()] = $reponseCritere;
        }

        $form = $this->createForm(QapesSaePart3Type::class, $qapesSae);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $qapesSaeRepository->add($qapesSae);
        }

        return $this->render('labset/qapes_sae/new_step3.html.twig', [
            'qapes' => $qapesSae,
            'form' => $form->createView(),
            'criteres' => $criteres,
            'reponses' =>$t
        ]);
    }


    #[Route('/{id}', name: 'app_qapes_sae_show', methods: ['GET'])]
    public function show(QapesSae $qapesSae): Response
    {
        return $this->render('labset/qapes_sae/show.html.twig', [
            'qapes' => $qapesSae,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_qapes_sae_edit', methods: ['GET', 'POST'])]
    public function edit(
        IutSiteRepository $iutSiteRepository,
        ApcParcoursRepository $parcoursRepository,
        DepartementRepository $departementRepository,
        ApcSaeRepository $apcSaeRepository,
        Request $request,
        QapesSae $qapesSae, QapesSaeRepository $qapesSaeRepository): Response
    {
        $form = $this->createForm(QapesSaePart1Type::class, $qapesSae,
        ['edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['qapes_sae_part1'];
            //iutSite
            $iutSite = $iutSiteRepository->find($data['iutSite'] );
            $qapesSae->setIutSite($iutSite);

            //parcours
            if ($data['parcours']  !== '') {
                $parcours = $parcoursRepository->find($data['parcours'] );
                $qapesSae->setParcours($parcours);
                $qapesSae->setSpecialite($parcours->getDepartement());
            }

            //specialite
            if ($data['specialite']  !== '') {
                $specialite = $departementRepository->find($data['specialite'] );
                $qapesSae->setSpecialite($specialite);
            }

            if ($data['sae'] !== '') {
                $sae = $apcSaeRepository->find($data['sae']);
                $qapesSae->setSae($sae);
            }

            $qapesSaeRepository->add($qapesSae);

            return $this->redirectToRoute('app_qapes_sae_new_etape_2', [
                'qapesSae' => $qapesSae->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('labset/qapes_sae/edit.html.twig', [
            'qapes' => $qapesSae,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_qapes_sae_delete', methods: ['POST'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Request $request, QapesSae $qapesSae, QapesSaeRepository $qapesSaeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $qapesSae->getId(), $request->request->get('_token'))) {
            foreach ($qapesSae->getQapesSaeCritereReponses() as $qapesSaeCritereReponse)
            {
                $entityManager->remove($qapesSaeCritereReponse);
            }
            $entityManager->flush();
            $qapesSaeRepository->remove($qapesSae);
        }

        return $this->redirectToRoute('app_labset', [], Response::HTTP_SEE_OTHER);
    }
}
