<?php

namespace App\Controller\labset;

use App\Entity\QapesSae;
use App\Entity\QapesSaeCritereReponse;
use App\Entity\User;
use App\Form\QapesSaePart1Type;
use App\Form\QapesSaePart2Type;
use App\Form\QapesSaePart3Type;
use App\Form\QapesSaePart4Type;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcSaeRepository;
use App\Repository\DepartementRepository;
use App\Repository\IutSiteParcoursRepository;
use App\Repository\IutSiteRepository;
use App\Repository\QapesCritereReponseRepository;
use App\Repository\QapesCritereRepository;
use App\Repository\QapesSaeRepository;
use App\Repository\UserRepository;
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


    #[Route('/new', name: 'app_qapes_sae_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
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
            $listeAuteurs = explode("\r\n",$data['redacteursAutres']);
            foreach ($listeAuteurs as $auteur) {
                $auteur = explode(";", $auteur);
                if (count($auteur) === 3) {
                    $exist = $userRepository->findOneBy(['email' => $auteur[2]]);
                    if ($exist === null) {
                        $user = new User();
                        $user->setNom($auteur[0]);
                        $user->setPrenom($auteur[1]);
                        $user->setPassword('labset');
                        $user->setEmail($auteur[2]);
                        $user->setRoles(['ROLE_LABSET']);
                        $entityManager->persist($user);
                        $qapesSae->addRedacteur($user);
                    } else {
                        $qapesSae->addRedacteur($exist);
                    }
                }
            }
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
    public function etape2(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Request $request, QapesSaeRepository $qapesSaeRepository, QapesSae $qapesSae): Response
    {
        $form = $this->createForm(QapesSaePart2Type::class, $qapesSae);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['qapes_sae_part2'];
            $listeAuteurs = explode("\r\n",$data['auteursAutres']);
            foreach ($listeAuteurs as $auteur) {
                $auteur = explode(";", $auteur);
                if (count($auteur) === 3) {
                    $exist = $userRepository->findOneBy(['email' => $auteur[2]]);
                    if ($exist === null) {
                        $user = new User();
                        $user->setNom($auteur[0]);
                        $user->setPrenom($auteur[1]);
                        $user->setPassword('labset');
                        $user->setEmail($auteur[2]);
                        $user->setRoles(['ROLE_LABSET']);
                        $entityManager->persist($user);
                        $qapesSae->addAuteur($user);
                    } else {
                        $qapesSae->addAuteur($exist);
                    }
                }
            }

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
        QapesCritereReponseRepository $qapesCritereReponseRepository,
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

            //mise à jour des critères
            foreach ($criteres as $critere) {
                //commentaire
                if ($request->request->has('commentaire_' . $critere->getId())) {
                    $commentaire = $request->request->get('commentaire_' . $critere->getId());
                } else {
                    $commentaire = '';
                }

                //réponse
                if ($request->request->has('reponse_' . $critere->getId())) {

                    $reponse = $qapesCritereReponseRepository->find($request->request->get('reponse_' . $critere->getId()));

                } else {
                    $reponse = null;
                }

                //si le critère existe dans $t, on le met à jour, sinon on le crée
                if (array_key_exists($critere->getId(), $t)) {
                    $t[$critere->getId()]->setReponse($reponse);
                    $t[$critere->getId()]->setCommentaire($commentaire);
                } else {
                    $critReponse = new QapesSaeCritereReponse();
                    $critReponse->setCritere($critere);
                    $critReponse->setReponse($reponse);
                    $critReponse->setCommentaire($commentaire);
                    $critReponse->setSae($qapesSae);
                    $qapesSae->addQapesSaeCritereReponse($critReponse);

                    $t[$critere->getId()] = $critReponse;
                }

                $qapesSaeRepository->add($qapesSae);

            }


            return $this->redirectToRoute('app_qapes_sae_new_etape_4', [
                'qapesSae' => $qapesSae->getId(),
            ]);
        }

        return $this->render('labset/qapes_sae/new_step3.html.twig', [
            'qapes' => $qapesSae,
            'form' => $form->createView(),
            'criteres' => $criteres,
            'reponses' =>$t
        ]);
    }

    #[Route('/new/etape-4/{qapesSae}', name: 'app_qapes_sae_new_etape_4', methods: ['GET', 'POST'])]
    public function etape4(
        QapesCritereRepository $qapesCritereRepository,
        Request $request,
        QapesSaeRepository $qapesSaeRepository,
        QapesSae $qapesSae
    ): Response {
        $form = $this->createForm(QapesSaePart4Type::class, $qapesSae);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $qapesSaeRepository->add($qapesSae);

            return $this->redirectToRoute('app_qapes_sae_show', ['id' => $qapesSae->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('labset/qapes_sae/new_step4.html.twig', [
            'qapes' => $qapesSae,
            'form' => $form->createView(),
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
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
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
            $listeAuteurs = explode("\r\n",$data['redacteursAutres']);
            foreach ($listeAuteurs as $auteur) {
                $auteur = explode(";", $auteur);
                if (count($auteur) === 3) {
                    $exist = $userRepository->findOneBy(['email' => $auteur[2]]);
                    if ($exist === null) {
                        $user = new User();
                        $user->setNom($auteur[0]);
                        $user->setPrenom($auteur[1]);
                        $user->setPassword('labset');
                        $user->setEmail($auteur[2]);
                        $user->setRoles(['ROLE_LABSET']);
                        $entityManager->persist($user);
                        $qapesSae->addRedacteur($user);
                    } else {
                        $qapesSae->addRedacteur($exist);
                    }
                }
            }


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
            foreach ($qapesSae->getQapesSaeCritereReponse() as $qapesSaeCritereReponse)
            {
                $entityManager->remove($qapesSaeCritereReponse);
            }
            $entityManager->flush();
            $qapesSaeRepository->remove($qapesSae);
        }

        return $this->redirectToRoute('app_labset', [], Response::HTTP_SEE_OTHER);
    }
}
