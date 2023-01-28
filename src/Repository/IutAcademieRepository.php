<?php

namespace App\Repository;

use App\Entity\IutAcademie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IutAcademie>
 *
 * @method IutAcademie|null find($id, $lockMode = null, $lockVersion = null)
 * @method IutAcademie|null findOneBy(array $criteria, array $orderBy = null)
 * @method IutAcademie[]    findAll()
 * @method IutAcademie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IutAcademieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IutAcademie::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(IutAcademie $entity, bool $flush = true): void
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
    public function remove(IutAcademie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return IutAcademie[] Returns an array of IutAcademie objects
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
    public function findOneBySomeField($value): ?IutAcademie
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
