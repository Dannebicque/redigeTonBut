<?php

namespace App\Repository;

use App\Entity\Departement;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findByUser(?UserInterface $user)
    {
        if ($user === null) {
            return [];
        }

        // tester si l'utilisateur est pacd du département
        if ($user->ispacd()) {
            return [$user->getDepartement()];
        }

        $qb = $this->createquerybuilder('version')
            ->innerjoin('version.departement', 'departement')
        ->andwhere(':user member of departement.cpns')
            ->setparameter('user', $user);


        return $qb->getquery()->getresult();

    }
}
