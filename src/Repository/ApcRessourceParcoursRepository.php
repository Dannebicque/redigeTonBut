<?php

namespace App\Repository;

use App\Entity\ApcRessourceParcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcRessourceParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcRessourceParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcRessourceParcours[]    findAll()
 * @method ApcRessourceParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcRessourceParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcRessourceParcours::class);
    }

    public function findArrayIdRessource(mixed $ressource)
    {

        $query = $this->createQueryBuilder('a')
            ->where('a.ressource = :ressource')
            ->setParameter('ressource', $ressource)
            ->getQuery()
            ->getResult();

        $t = [];
        foreach ($query as $q) {
            $t[] = $q->getRessource()->getId();
        }

        return $t;

    }
}
