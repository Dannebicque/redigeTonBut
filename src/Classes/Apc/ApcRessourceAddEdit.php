<?php


namespace App\Classes\Apc;


use App\Entity\ApcRessource;
use App\Entity\ApcRessourceApprentissageCritique;
use App\Entity\ApcRessourceParcours;
use App\Entity\ApcSaeRessource;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcParcoursRepository;

use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;

class ApcRessourceAddEdit
{


    private EntityManagerInterface $entityManager;
    private ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository;
    private ApcSaeRepository $apcSaeRepository;
    private ApcParcoursRepository $apcParcoursRepository;
    private ApcRessourceRepository $apcRessourceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcParcoursRepository = $apcParcoursRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
    }


    public function addOrEdit(ApcRessource $apcRessource, $request) {
        $apcRessource->setCodeMatiere(Codification::codeRessource($apcRessource));
        $this->entityManager->persist($apcRessource);

        $acs = $request->request->get('ac');
        if (is_array($acs)) {
            foreach ($acs as $idAc) {
                $ac = $this->apcApprentissageCritiqueRepository->find($idAc);
                $saeAc = new ApcRessourceApprentissageCritique($apcRessource, $ac);
                $this->entityManager->persist($saeAc);
            }
        }

        $saes = $request->request->get('saes');
        if (is_array($saes)) {
            foreach ($saes as $idAc) {
                $apcSae = $this->apcSaeRepository->find($idAc);
                $saeRes = new ApcSaeRessource($apcSae, $apcRessource);
                $this->entityManager->persist($saeRes);
            }
        }

        $parcours = $request->request->get('parcours');
        if (is_array($parcours)) {
            foreach ($parcours as $idParcours) {
                $parc = $this->apcParcoursRepository->find($idParcours);
                $saeAc = new ApcRessourceParcours($apcRessource, $parc);
                $this->entityManager->persist($saeAc);
            }
        }

        $tprerequis = $request->request->get('tprerequis');
        if (is_array($tprerequis)) {
            foreach ($tprerequis as $idAc) {
                $res = $this->apcRessourceRepository->find($idAc);
                if ($res !== null) {
                    $apcRessource->addRessourcesPreRequise($res);
                }
            }
        }

        $this->entityManager->flush();
    }

    public function removeLiens(ApcRessource $apcRessource)
    {
        foreach ($apcRessource->getApcRessourceApprentissageCritiques() as $ac) {
            $this->entityManager->remove($ac);
        }
        foreach ($apcRessource->getApcRessourceParcours() as $ac) {
            $this->entityManager->remove($ac);
        }
        foreach ($apcRessource->getRessourcesPreRequises() as $ac) {
            $apcRessource->removeRessourcesPreRequise($ac);
            $ac->removeApcRessource($apcRessource);
        }
        foreach ($apcRessource->getApcSaeRessources() as $ac) {
            $this->entityManager->remove($ac);
        }
    }
}
