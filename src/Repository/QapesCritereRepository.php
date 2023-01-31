<?php

namespace App\Repository;

use App\Entity\QapesCritere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QapesCritere>
 *
 * @method QapesCritere|null find($id, $lockMode = null, $lockVersion = null)
 * @method QapesCritere|null findOneBy(array $criteria, array $orderBy = null)
 * @method QapesCritere[]    findAll()
 * @method QapesCritere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QapesCritereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QapesCritere::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(QapesCritere $entity, bool $flush = true): void
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
    public function remove(QapesCritere $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return QapesCritere[] Returns an array of QapesCritere objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?QapesCritere
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
