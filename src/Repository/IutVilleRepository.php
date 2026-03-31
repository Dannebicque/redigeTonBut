<?php

namespace App\Repository;

use App\Entity\IutVille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IutVille>
 *
 * @method IutVille|null find($id, $lockMode = null, $lockVersion = null)
 * @method IutVille|null findOneBy(array $criteria, array $orderBy = null)
 * @method IutVille[]    findAll()
 * @method IutVille[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IutVilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IutVille::class);
    }

    /**
     */
    public function add(IutVille $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     */
    public function remove(IutVille $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
