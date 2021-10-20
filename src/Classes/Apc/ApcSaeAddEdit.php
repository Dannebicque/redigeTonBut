<?php


namespace App\Classes\Apc;


use App\Entity\ApcSae;
use App\Entity\ApcSaeApprentissageCritique;
use App\Entity\ApcSaeCompetence;
use App\Entity\ApcSaeParcours;
use App\Entity\ApcSaeRessource;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcComptenceRepository;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;

class ApcSaeAddEdit
{
    private EntityManagerInterface $entityManager;
    private ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository;
    private ApcParcoursRepository $apcParcoursRepository;
    private ApcRessourceRepository $apcRessourceRepository;
    private ApcComptenceRepository $apcComptenceRepository;

    private array $tabCoeffs = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcParcoursRepository $apcParcoursRepository,
        ApcComptenceRepository $apcComptenceRepository,
        ApcRessourceRepository $apcRessourceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;
        $this->apcParcoursRepository = $apcParcoursRepository;
        $this->apcComptenceRepository = $apcComptenceRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
    }


    public function addOrEdit(ApcSae $apcSae, $request)
    {
        $this->entityManager->persist($apcSae);
        $tabAcComp = [];
        //sauvegarde des AC
        $acs = $request->request->get('ac');
        if (is_array($acs)) {
            foreach ($acs as $idAc) {
                $ac = $this->apcApprentissageCritiqueRepository->find($idAc);
                if ($ac !== null) {
                    $saeAc = new ApcSaeApprentissageCritique($apcSae, $ac);
                    $this->entityManager->persist($saeAc);
                    if (!in_array($ac->getCompetence()->getId(), $tabAcComp, true)) {
                        $tabAcComp[] = $ac->getCompetence()->getId();
                    }
                }
            }
        }

        foreach ($tabAcComp as $idCompetence) {
            $ac = $this->apcComptenceRepository->find($idCompetence);
            $saeAc = new ApcSaeCompetence($apcSae, $ac);
            if (array_key_exists($idCompetence, $this->tabCoeffs)) {
                $saeAc->setCoefficient($this->tabCoeffs[$idCompetence]);
            }
            $this->entityManager->persist($saeAc);
        }


        $parcours = $request->request->get('parcours');
        if (is_array($parcours)) {
            foreach ($parcours as $idParcours) {
                $parc = $this->apcParcoursRepository->find($idParcours);
                if ($parc !== null) {
                    $saeAc = new ApcSaeParcours($apcSae, $parc);
                    $this->entityManager->persist($saeAc);
                }
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

        foreach ($apcSae->getApcSaeCompetences() as $ac) {
            $this->tabCoeffs[$ac->getCompetence()->getId()] = $ac->getCoefficient();
            $this->entityManager->remove($ac);
        }
        foreach ($apcSae->getApcSaeParcours() as $ac) {
            $this->entityManager->remove($ac);
        }
        foreach ($apcSae->getApcSaeRessources() as $ac) {
            $this->entityManager->remove($ac);
        }

        $this->entityManager->flush();

    }

    public function duplique(ApcSae $apcSae): ApcSae
    {
        $sae = clone $apcSae;
        $this->entityManager->persist($sae);
        $this->entityManager->flush();

        foreach ($apcSae->getApcSaeApprentissageCritiques() as $ac) {
            $newAc = new ApcSaeApprentissageCritique($sae, $ac->getApprentissageCritique());
            $this->entityManager->persist($newAc);

        }

        foreach ($apcSae->getApcSaeCompetences() as $ac) {
            $newAc = new ApcSaeCompetence($sae, $ac->getCompetence());
            $this->entityManager->persist($newAc);

        }

        foreach ($apcSae->getApcSaeParcours() as $ac) {
            $newAc = new ApcSaeParcours($sae, $ac->getParcours());
            $this->entityManager->persist($newAc);
        }

        foreach ($apcSae->getApcSaeRessources() as $ac) {
            $newAc = new ApcSaeRessource($sae, $ac->getRessource());
            $this->entityManager->persist($newAc);
        }
        $this->entityManager->flush();

        return $sae;
    }

}
