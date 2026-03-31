<?php

namespace App\Repository;

use App\Entity\IutSiteParcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IutSiteParcours>
 *
 * @method IutSiteParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method IutSiteParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method IutSiteParcours[]    findAll()
 * @method IutSiteParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IutSiteParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IutSiteParcours::class);
    }

    /**
     */
    public function add(IutSiteParcours $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     */
    public function remove(IutSiteParcours $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
