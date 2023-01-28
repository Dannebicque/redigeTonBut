<?php

namespace App\Repository;

use App\Entity\IutSiteParcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(IutSiteParcours $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(IutSiteParcours $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return IutSiteParcours[] Returns an array of IutSiteParcours objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IutSiteParcours
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
