<?php

namespace App\Controller\labset;


use App\Entity\QapesCritere;
use App\Repository\QapesCritereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/labset/criteres/evaluation')]
class QapesCriteresEvaluationController extends AbstractController
{
    #[Route('/', name: 'app_qapes_criteres_evaluation_index', methods: ['GET'])]
    public function index(QapesCritereRepository $qapesCriteresEvaluationRepository): Response
    {
        return $this->render('labset/qapes_criteres_evaluation/index.html.twig', [
            'qapes_criteres_evaluations' => $qapesCriteresEvaluationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_qapes_criteres_evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, QapesCritereRepository $qapesCriteresEvaluationRepository): Response
    {
        $qapesCriteresEvaluation = new QapesCritere();
        $form = $this->createForm(QapesCriteresEvaluation1Type::class, $qapesCriteresEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $qapesCriteresEvaluationRepository->add($qapesCriteresEvaluation);
            return $this->redirectToRoute('app_qapes_criteres_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('labset/qapes_criteres_evaluation/new.html.twig', [
            'qapes_criteres_evaluation' => $qapesCriteresEvaluation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_qapes_criteres_evaluation_show', methods: ['GET'])]
    public function show(QapesCriteresEvaluation $qapesCriteresEvaluation): Response
    {
        return $this->render('labset/qapes_criteres_evaluation/show.html.twig', [
            'qapes_criteres_evaluation' => $qapesCriteresEvaluation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_qapes_criteres_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, QapesCriteresEvaluation $qapesCriteresEvaluation, QapesCriteresEvaluationRepository $qapesCriteresEvaluationRepository): Response
    {
        $form = $this->createForm(QapesCriteresEvaluation1Type::class, $qapesCriteresEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $qapesCriteresEvaluationRepository->add($qapesCriteresEvaluation);
            return $this->redirectToRoute('app_qapes_criteres_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('labset/qapes_criteres_evaluation/edit.html.twig', [
            'qapes_criteres_evaluation' => $qapesCriteresEvaluation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_qapes_criteres_evaluation_delete', methods: ['POST'])]
    public function delete(Request $request, QapesCriteresEvaluation $qapesCriteresEvaluation, QapesCriteresEvaluationRepository $qapesCriteresEvaluationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$qapesCriteresEvaluation->getId(), $request->request->get('_token'))) {
            $qapesCriteresEvaluationRepository->remove($qapesCriteresEvaluation);
        }

        return $this->redirectToRoute('app_qapes_criteres_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }
}
