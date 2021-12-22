<?php


namespace App\Classes\Apc;


use App\Entity\ApcRessource;
use App\Entity\ApcRessourceApprentissageCritique;
use App\Entity\ApcRessourceCompetence;
use App\Entity\ApcRessourceParcours;
use App\Entity\ApcSaeRessource;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcComptenceRepository;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ApcRessourceAddEdit
{


    private EntityManagerInterface $entityManager;
    private ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository;
    private ApcSaeRepository $apcSaeRepository;
    private ApcParcoursRepository $apcParcoursRepository;
    private ApcRessourceRepository $apcRessourceRepository;
    private ApcComptenceRepository $apcComptenceRepository;
    private array $tabCoeffs = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        ApcComptenceRepository $apcComptenceRepository,
        ApcSaeRepository $apcSaeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        ApcRessourceRepository $apcRessourceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;
        $this->apcSaeRepository = $apcSaeRepository;
        $this->apcParcoursRepository = $apcParcoursRepository;
        $this->apcComptenceRepository = $apcComptenceRepository;
        $this->apcRessourceRepository = $apcRessourceRepository;
    }


    public function addOrEdit(ApcRessource $apcRessource, $request, $verouillee = false)
    {
        $this->entityManager->persist($apcRessource);

        if ($verouillee === false) {
            $tabAcComp = [];

            $acs = $request->request->get('ac');
            if (is_array($acs)) {
                foreach ($acs as $idAc) {
                    $ac = $this->apcApprentissageCritiqueRepository->find($idAc);
                    if ($ac !== null) {
                        $saeAc = new ApcRessourceApprentissageCritique($apcRessource, $ac);
                        $this->entityManager->persist($saeAc);
                        if (!in_array($ac->getCompetence()->getId(), $tabAcComp, true)) {
                            $tabAcComp[] = $ac->getCompetence()->getId();
                        }
                    }
                }
            }

            foreach ($tabAcComp as $idCompetence) {
                $ac = $this->apcComptenceRepository->find($idCompetence);
                $saeAc = new ApcRessourceCompetence($apcRessource, $ac);
                if (array_key_exists($idCompetence, $this->tabCoeffs)) {
                    $saeAc->setCoefficient($this->tabCoeffs[$idCompetence]);
                }
                $this->entityManager->persist($saeAc);

            }

            $parcours = $request->request->get('parcours');
            if (is_array($parcours)) {
                foreach ($parcours as $idParcours) {
                    $parc = $this->apcParcoursRepository->find($idParcours);
                    $saeAc = new ApcRessourceParcours($apcRessource, $parc);
                    $this->entityManager->persist($saeAc);
                }
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

    public function removeLiens(ApcRessource $apcRessource, $verouille = false)
    {
        if ($verouille === false) {
            foreach ($apcRessource->getApcRessourceApprentissageCritiques() as $ac) {
                $this->entityManager->remove($ac);
            }

            foreach ($apcRessource->getApcRessourceCompetences() as $ac) {
                $this->tabCoeffs[$ac->getCompetence()->getId()] = $ac->getCoefficient();
                $this->entityManager->remove($ac);
            }

            foreach ($apcRessource->getApcRessourceParcours() as $ac) {
                $this->entityManager->remove($ac);
            }
        }


        foreach ($apcRessource->getRessourcesPreRequises() as $ac) {
            $apcRessource->removeRessourcesPreRequise($ac);
            $ac->removeApcRessource($apcRessource);
        }
        foreach ($apcRessource->getApcSaeRessources() as $ac) {
            $this->entityManager->remove($ac);
        }
        $this->entityManager->flush();
    }

    public function duplique(ApcRessource $apcRessource): ApcRessource
    {
        $ressource = clone $apcRessource;
        $this->entityManager->persist($ressource);
        $this->entityManager->flush();

        foreach ($apcRessource->getApcRessourceApprentissageCritiques() as $ac) {
            $newAc = new ApcRessourceApprentissageCritique($ressource, $ac->getApprentissageCritique());
            $this->entityManager->persist($newAc);

        }

        foreach ($apcRessource->getApcRessourceCompetences() as $ac) {
            $newAc = new ApcRessourceCompetence($ressource, $ac->getCompetence());
            $this->entityManager->persist($newAc);

        }

        foreach ($apcRessource->getApcRessourceParcours() as $ac) {
            $newAc = new ApcRessourceParcours($ressource, $ac->getParcours());
            $this->entityManager->persist($newAc);
        }

        foreach ($apcRessource->getRessourcesPreRequises() as $ac) {
            $ressource->addRessourcesPreRequise($ac);
            $ac->addApcRessource($ressource);
        }

        foreach ($apcRessource->getApcSaeRessources() as $ac) {
            $newAc = new ApcSaeRessource($ac->getSae(), $ressource);
            $this->entityManager->persist($newAc);
        }
        $this->entityManager->flush();

        return $ressource;
    }
}
