<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcSaeCompetenceRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 16:46
 */

namespace App\Repository;

use App\Entity\ApcCompetence;
use App\Entity\ApcSae;
use App\Entity\ApcSaeCompetence;
use App\Entity\Departement;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcSaeCompetence|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcSaeCompetence|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcSaeCompetence[]    findAll()
 * @method ApcSaeCompetence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcSaeCompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcSaeCompetence::class);
    }

    public function findBySemestre(Semestre $semestre)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin(ApcSae::class, 's', 'WITH', 'c.sae = s.id')
            ->where('s.semestre = :semestre')
            ->setParameter('semestre', $semestre->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin(ApcCompetence::class, 'comp', 'WITH', 'c.competence = comp.id')
            ->innerJoin(ApcSae::class, 's', 'WITH', 'c.sae = s.id')
            ->where('comp.departement = :departement')
            ->setParameter('departement', $departement->getId())
            ->getQuery()
            ->getResult();
    }

    public function findBySaeArray($sae)
    {
        $res = $this->findBy(['sae' => $sae]);
        $t = [];

        foreach ($res as $r) {
            $t[] = $r->getCompetence()->getId();
        }

        return $t;
    }
}
