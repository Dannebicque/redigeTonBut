<?php


namespace App\Classes\Apc;


use App\Entity\ApcRessource;
use App\Entity\ApcRessourceApprentissageCritique;
use App\Entity\ApcRessourceParcours;
use App\Entity\ApcSae;
use App\Entity\ApcSaeApprentissageCritique;
use App\Entity\ApcSaeParcours;
use App\Entity\ApcSaeRessource;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;

class ApcSaeAddEdit
{
    private EntityManagerInterface $entityManager;
    private ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository;
    private ApcParcoursRepository $apcParcoursRepository;
    private ApcRessourceRepository $apcRessourceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcParcoursRepository $apcParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;
        $this->apcParcoursRepository = $apcParcoursRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
    }


    public function addOrEdit(ApcSae $apcSae, $request) {
        $this->entityManager->persist($apcSae);
        //sauvegarde des AC
        $acs = $request->request->get('ac');
        if (is_array($acs)) {
            foreach ($acs as $idAc) {
                $ac = $this->apcApprentissageCritiqueRepository->find($idAc);
                $saeAc = new ApcSaeApprentissageCritique($apcSae, $ac);
                $this->entityManager->persist($saeAc);
            }
        }

        $parcours = $request->request->get('parcours');
        if (is_array($parcours)) {
            foreach ($parcours as $idParcours) {
                $parc = $this->apcParcoursRepository->find($idParcours);
                $saeAc = new ApcSaeParcours($apcSae, $parc);
                $this->entityManager->persist($saeAc);
            }
        }

        $acs = $request->request->get('ressources');
        if (is_array($acs)) {
            foreach ($acs as $idAc) {
                $res = $this->apcRessourceRepository->find($idAc);
                $saeRes = new ApcSaeRessource($apcSae, $res);
                $this->entityManager->persist($saeRes);
            }
        }
        $apcSae->setCodeMatiere(Codification::codeSae($apcSae));

        $this->entityManager->flush();
    }

    public function removeLiens(ApcSae $apcSae)
    {
//on supprimer ceux présent
        foreach ($apcSae->getApcSaeApprentissageCritiques() as $ac) {
            $this->entityManager->remove($ac);
        }
        foreach ($apcSae->getApcSaeParcours() as $ac) {
            $this->entityManager->remove($ac);
        }
        foreach ($apcSae->getApcSaeRessources() as $ac) {
            $this->entityManager->remove($ac);
        }
    }
}
