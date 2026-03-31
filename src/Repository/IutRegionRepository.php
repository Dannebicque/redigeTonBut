<?php

namespace App\Repository;

use App\Entity\IutRegion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IutRegion>
 *
 * @method IutRegion|null find($id, $lockMode = null, $lockVersion = null)
 * @method IutRegion|null findOneBy(array $criteria, array $orderBy = null)
 * @method IutRegion[]    findAll()
 * @method IutRegion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IutRegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IutRegion::class);
    }

    /**
     */
    public function add(IutRegion $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     */
    public function remove(IutRegion $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
