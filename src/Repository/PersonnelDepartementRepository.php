<?php

namespace App\Repository;

use App\Entity\Departement;
use App\Entity\PersonnelDepartement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PersonnelDepartement|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonnelDepartement|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonnelDepartement[]    findAll()
 * @method PersonnelDepartement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonnelDepartementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonnelDepartement::class);
    }


    public function findByDepartement(Departement $departement)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin(User::class, 'u', 'WITH', 'u.id = p.user')
            ->where('p.Departement = :departement')
            ->setParameter('departement', $departement->getId())
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
