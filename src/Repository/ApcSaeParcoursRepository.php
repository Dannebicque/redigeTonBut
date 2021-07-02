<?php

namespace App\Repository;

use App\Entity\ApcSaeParcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcSaeParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcSaeParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcSaeParcours[]    findAll()
 * @method ApcSaeParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcSaeParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcSaeParcours::class);
    }

    public function findArrayIdSae(mixed $sae)
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.sae = :sae')
            ->setParameter('sae', $sae)
            ->getQuery()
            ->getResult();

        $t = [];
        foreach ($query as $q) {
            $t[] = $q->getSae()->getId();
        }

        return $t;
    }
}
