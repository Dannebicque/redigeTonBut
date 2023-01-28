<?php

namespace App\Repository;

use App\Entity\IutUniversite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IutUniversite>
 *
 * @method IutUniversite|null find($id, $lockMode = null, $lockVersion = null)
 * @method IutUniversite|null findOneBy(array $criteria, array $orderBy = null)
 * @method IutUniversite[]    findAll()
 * @method IutUniversite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IutUniversiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IutUniversite::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(IutUniversite $entity, bool $flush = true): void
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
    public function remove(IutUniversite $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return IutUniversite[] Returns an array of IutUniversite objects
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
    public function findOneBySomeField($value): ?IutUniversite
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
