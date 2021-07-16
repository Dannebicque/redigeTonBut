<?php
namespace App\Classes\Apc;

use App\Entity\ApcCompetence;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcComptenceRepository;
use App\Utils\Codification;
use Doctrine\ORM\EntityManagerInterface;

class ApcCompetenceOrdre
{
    private EntityManagerInterface $entityManager;
    private ApcComptenceRepository $apcComptenceRepository;
    private ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository;


    public function __construct(EntityManagerInterface $entityManager, ApcComptenceRepository $apcComptenceRepository, ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository)
    {
        $this->entityManager = $entityManager;
        $this->apcComptenceRepository = $apcComptenceRepository;
        $this->apcApprentissageCritiqueRepository = $apcApprentissageCritiqueRepository;

    }

    public function deplaceCompetence(ApcCompetence $apcCompetence, string $ordreInitial)
    {
        //modifie l'ordre de la ressource
        $ordreDestination = $apcCompetence->getCouleur();

        //récupère toutes les ressources à déplacer
        return $this->inverse($ordreInitial, $ordreDestination, $apcCompetence);
    }

    private function inverse(string $ordreInitial, string $ordreDestination, ApcCompetence $apcCompetence): bool
    {
        $competences = $this->apcComptenceRepository->findOther(
            $ordreDestination,
            $apcCompetence
        );
        $apcCompetence->setCouleur($ordreDestination);
        $apcCompetence->setNumero((int)$ordreDestination[1]);
        $this->codifieAc($apcCompetence);


        foreach($competences as $competence) {
            $competence->setCouleur($ordreInitial);
            $competence->setNumero((int)$ordreInitial[1]);
            $this->codifieAc($competence);

        }

        $this->entityManager->flush();


        return true;
    }

    private function codifieAc(ApcCompetence $competence)
    {
        $acs = $this->apcApprentissageCritiqueRepository->findByCompetence($competence);

        foreach ($acs as $ac)
        {
            $ac->setCode(Codification::codeApprentissageCritique($ac));
        }
        $this->entityManager->flush();
    }
}
