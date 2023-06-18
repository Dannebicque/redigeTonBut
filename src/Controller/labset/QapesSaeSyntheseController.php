<?php

namespace App\Controller\labset;

use App\Repository\QapesCritereRepository;
use App\Repository\QapesSaeCritereReponseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/labset/qapes/synthese')]
class QapesSaeSyntheseController extends AbstractController
{
    #[Route('/', name: 'app_labset_synthese')]
    public function index(
        QapesSaeCritereReponseRepository $qapesSaeCritereReponseRepository,
        QapesCritereRepository           $qapesCritereRepository,
        ChartBuilderInterface            $chartBuilder
    )
    {
        $total = [];
        $reponses = $qapesSaeCritereReponseRepository->findAll();
        foreach ($reponses as $reponse) {
            if ($reponse->getReponse() !== null && $reponse->getCritere() !== null) {
                if (!array_key_exists($reponse->getCritere()->getId(), $total)) {
                    $total[$reponse->getCritere()?->getId()] = [];
                }

                if (!array_key_exists($reponse->getReponse()->getId(), $total[$reponse->getCritere()->getId()])) {

                    $total[$reponse->getCritere()->getId()][$reponse->getReponse()->getId()] = 0;
                }

                $total[$reponse->getCritere()->getId()][$reponse->getReponse()->getId()] += 1;
            }
        }


        $criteres = $qapesCritereRepository->findBy([], ['libelle' => 'ASC']);
        $tabCharts = [];
        foreach ($criteres as $critere) {
            $tabCharts[$critere->getId()] = $chartBuilder->createChart(Chart::TYPE_PIE);
            $labels = [];
            $data = [];
            $backgroundColor = [];

            foreach ($critere->getQapesCritereReponses() as $reponse) {
                $labels[$reponse->getId()] = $reponse->getLibelle();
                $data[$reponse->getId()] = $total[$critere->getId()][$reponse->getId()] ?? 0;
                $color = list($r, $g, $b) = sscanf($reponse->getCouleur(), "#%02x%02x%02x");
                $backgroundColor[$reponse->getId()] = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
            }

            $tabCharts[$critere->getId()]->setData([
                'labels' => array_values($labels),
                'datasets' => [
                    [
                        'data' => array_values($data),
                        'backgroundColor' => array_values($backgroundColor),
                    ],
                ],
            ]);

            dump($tabCharts[$critere->getId()]);
            // die();

//            $tabCharts[$critere->getId()]->setOptions([
//                'scales' => [
//                    'y' => [
//                        'suggestedMin' => 0,
//                        'suggestedMax' => 100,
//                    ],
//                ],
//            ]);
        }


        return $this->render('labset/qapes_sae_synthese/index.html.twig', [
            'criteres' => $criteres,
            'charts' => $tabCharts
        ]);
    }
}
