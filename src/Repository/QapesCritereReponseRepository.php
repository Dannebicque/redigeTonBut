<?php

namespace App\Repository;

use App\Entity\QapesCritereReponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QapesCritereReponse>
 *
 * @method QapesCritereReponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method QapesCritereReponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method QapesCritereReponse[]    findAll()
 * @method QapesCritereReponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QapesCritereReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QapesCritereReponse::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(QapesCritereReponse $entity, bool $flush = true): void
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
    public function remove(QapesCritereReponse $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return QapesCritereReponse[] Returns an array of QapesCritereReponse objects
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
    public function findOneBySomeField($value): ?QapesCritereReponse
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
