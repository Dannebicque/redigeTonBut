<?php

namespace App\Repository;

use App\Entity\IutAcademie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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
     */
    public function add(IutAcademie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     */
    public function remove(IutAcademie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
