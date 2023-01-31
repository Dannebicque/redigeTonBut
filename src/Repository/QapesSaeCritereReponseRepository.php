<?php

namespace App\Repository;

use App\Entity\QapesSaeCritereReponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QapesSaeCritereReponse>
 *
 * @method QapesSaeCritereReponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method QapesSaeCritereReponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method QapesSaeCritereReponse[]    findAll()
 * @method QapesSaeCritereReponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QapesSaeCritereReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QapesSaeCritereReponse::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(QapesSaeCritereReponse $entity, bool $flush = true): void
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
    public function remove(QapesSaeCritereReponse $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
