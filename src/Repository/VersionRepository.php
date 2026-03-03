<?php

namespace App\Repository;

use App\Entity\Departement;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Version>
 */
class VersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Version::class);
    }

    public function findByVersion(int $annee): array
    {
        return $this->createQueryBuilder('v')
            ->innerJoin('v.departement', 'd')
            ->where('v.annee = :annee')
            ->setParameter('annee', $annee)
            ->orderBy('d.numeroAnnexe', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByAnneeAndDepartement(?Departement $getDepartement, int $versionPn): Version
    {
        return $this->createQueryBuilder('v')
            ->where('v.annee = :annee')
            ->andWhere('v.departement = :departement')
            ->setParameter('annee', $versionPn)
            ->setParameter('departement', $getDepartement?->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
