<?php

namespace App\Repository;

use App\Entity\ApcCompetenceSemestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcCompetenceSemestre|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcCompetenceSemestre|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcCompetenceSemestre[]    findAll()
 * @method ApcCompetenceSemestre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcCompetenceSemestreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcCompetenceSemestre::class);
    }
}
