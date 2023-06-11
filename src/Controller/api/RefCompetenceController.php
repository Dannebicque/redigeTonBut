<?php

namespace App\Controller\api;

use App\Classes\Export\DepartementExport;
use App\Controller\BaseApiController;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RefCompetenceController extends BaseApiController
{
    //todo: plus utile avec la nouvelle version de l'api
//    #[Route(path: '/api/{specialite}', name: 'api_api')]
//    public function competences(
//        DepartementExport     $departementExport,
//        DepartementRepository $departementRepository,
//        string                $specialite
//    )
//    {
//        // $this->checkAccessApi($request);
//
//        $departement = $departementRepository->findOneBy(['sigle' => $specialite]);
//
//        if ($departement === null) {
//            throw new NotFoundHttpException();
//        }
//
//        $competences = $departement->getApcCompetences();
//        $parcours = $departement->getApcParcours();
//
//        $refCompetences = ['specialite' => [
//            'specialite' => $departement->getSigle(),
//            'specialite_long' => $departement->getLibelle(),
//            'type' => "B.U.T.",
//            'annexe' => $departement->getNumeroAnnexe(),
//            'type_structure' => $departement->getTypeStructure(),
//            'type_departement' => $departement->getTypeDepartement(),
//            'version' => $departement->getDateVersionCompetence() != null ? $departement->getDateVersionCompetence()->format('Y-m-d H:i:s') : '-'
//        ],
//            'competences' => [],
//            'parcours' => [],
//            'parcoursNiveaux' => [],
//        ];
//
//        foreach ($competences as $competence) {
//            $comp = [
//                'nom_court' => $competence->getNomCourt(),
//                'numero' => $competence->getNumero(),
//                'libelle_long' => $competence->getLibelle(),
//                'couleur' => $competence->getCouleur(),
//                'id' => $competence->getCleUnique(),
//                'situations' => [],
//                'composantes' => [],
//                'niveaux' => []
//            ];
//
//            foreach ($competence->getApcSituationProfessionnelles() as $situation) {
//                $comp['situations'][] = $situation->getLibelle();
//            }
//
//            foreach ($competence->getApcComposanteEssentielles() as $composante) {
//                $comp['composantes'][] = $composante->getLibelle();
//            }
//
//            foreach ($competence->getApcNiveaux() as $niveau) {
//                $niv = [
//                    'ordre' => $niveau->getOrdre(),
//                    'libelle' => $niveau->getLibelle(),
//                    'annee' => 'BUT' . ($niveau->getAnnee() !== null ? $niveau->getAnnee()->getOrdre() : $niveau->getOrdre()),
//                    'acs' => []
//                ];
//
//                foreach ($niveau->getApcApprentissageCritiques() as $ac) {
//                    $niv['acs'][$ac->getCode()] = $ac->getLibelle();
//                }
//
//                $comp['niveaux'][$niveau->getOrdre()] = $niv;
//            }
//
//            $refCompetences['competences'][] = $comp;
//        }
//
//        foreach ($parcours as $parcour) {
//            $parc = [
//                'numero' => $parcour->getOrdre(),
//                'libelle' => $parcour->getLibelle(),
//                'code' => $parcour->getCode()
//            ];
//
//            $refCompetences['parcours'][] = $parc;
//            $refCompetences['parcoursNiveaux'][$parcour->getId()] = [];
//            foreach ($parcour->getApcParcoursNiveaux() as $pn) {
//
//                $apcNiveau = $pn->getNiveau();
////                $refCompetences['parcoursNiveaux'][$pn->getParcours()->getId()][$apcNiveau->getOrdre()] = $apcNiveau->getId();
//                //todo: a reprendre
//            }
//        }
//
////    <parcours >
////        {
////            %
////            for parcour in parcours %}
////        <parcour numero = "{{ parcour.ordre }}" libelle = "{{ parcour.libelle }}" code = "{{ parcour.code }}" >
////            {
////                %
////                for annee in 1..3 %}
////            <annee ordre = "{{ annee }}" >
////                {
////                    %
////                    for niveau in parcour . apcParcoursNiveaux | filter(niveau => ((not(niveau . niveau . annee is
////                    defined) and (niveau . niveau . ordre == annee)) or ((niveau . niveau . annee is
////                    defined) and (niveau . niveau . annee != null and niveau . niveau . annee . ordre == annee)))) %}
////                <competence niveau = "{{ niveau.niveau.ordre }}" id = "{{ niveau.niveau.competence.cleUnique }}" />
////                {
////                    % endfor %
////                }
////            </annee >
////            {
////                % endfor %
////            }
////        </parcour >
////        {
////            % endfor %
////        }
////    </parcours >
//
//
//        return $this->json($refCompetences);
//    }
}
