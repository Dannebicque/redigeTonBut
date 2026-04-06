<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcComptenceRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 16:47
 */

namespace App\Repository;

use App\Entity\ApcCompetence;
use App\Entity\Semestre;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcCompetence|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcCompetence|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcCompetence[]    findAll()
 * @method ApcCompetence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcComptenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcCompetence::class);
    }

    public function findByVersion(Version $version)
    {
        return $this->findByVersionBuilder($version)
            ->getQuery()
            ->getResult();
    }


    //todo: gérer la version ?
    public function findBySigleVersion($departement): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.version', 'd')
            ->where('d.sigle = :sigle')
            ->setParameter('sigle', $departement)
            ->orderBy('c.couleur', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByVersionBuilder(Version $version): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->where('c.version = :version')
            ->setParameter('version', $version->getId())
            ->orderBy('c.couleur', 'ASC')
            ;
    }

    public function findOneByVersionArray(Version $version): array
    {
        $comps = $this->findByVersion($version);
        $t = [];
        foreach ($comps as $c) {
            $t[$c->getNomCourt()] = $c;
        }

        return $t;
    }

    public function findByVersionArray(Version $version): array
    {
        $comps = $this->findByVersion($version);
        $t = [];
        foreach ($comps as $c) {
            $t[$c->getCouleur()] = $c;
        }

        return $t;
    }

    public function findOther(string $ordreDestination, ApcCompetence $competence)
    {
        return $this->createQueryBuilder('a')
            ->where('a.couleur = :couleur')
            ->andWhere('a.version = :version')
            ->andWhere('a.id != :id')
            ->setParameter('couleur', $ordreDestination)
            ->setParameter('version', $competence->getVersion()->getId())
            ->setParameter('id', $competence->getId())
            ->getQuery()
            ->getResult();
    }

    public function findLastByVersion(?Version $version): ?ApcCompetence
    {
        return $this->createQueryBuilder('c')
            ->where('c.version = :version')
            ->setParameter('version', $version?->getId())
            ->orderBy('c.numero', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countBySemestre(Semestre $semestre)
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.apcCompetenceSemestres', 'cs')
            ->where('cs.semestre = :semestre')
            ->setParameter('semestre', $semestre->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

}
