<?php

namespace App\Repository;

use App\Entity\IutSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IutSite>
 *
 * @method IutSite|null find($id, $lockMode = null, $lockVersion = null)
 * @method IutSite|null findOneBy(array $criteria, array $orderBy = null)
 * @method IutSite[]    findAll()
 * @method IutSite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IutSiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IutSite::class);
    }

    /**
     */
    public function add(IutSite $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     */
    public function remove(IutSite $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
