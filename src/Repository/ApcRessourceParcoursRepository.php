<?php

namespace App\Repository;

use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\ApcRessourceParcours;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcRessourceParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcRessourceParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcRessourceParcours[]    findAll()
 * @method ApcRessourceParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcRessourceParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcRessourceParcours::class);
    }

    public function findArrayIdRessource(mixed $ressource)
    {

        $query = $this->createQueryBuilder('a')
            ->where('a.ressource = :ressource')
            ->setParameter('ressource', $ressource)
            ->getQuery()
            ->getResult();

        $t = [];
        foreach ($query as $q) {
            $t[] = $q->getRessource()->getId();
        }

        return $t;

    }

    public function findBySemestre(Semestre $semestre, ApcParcours $parcours)
    {
        $req = $this->createQueryBuilder('p')
            ->innerJoin(ApcRessource::class, 's', 'WITH', 'p.ressource = s.id')
            ->where('p.parcours = :parcours')
            ->andWhere('s.semestre = :semestre')
            ->setParameter('parcours', $parcours->getId())
            ->setParameter('semestre', $semestre->getId())
            ->orderBy('s.ordre', 'ASC')
            ->addOrderBy('s.codeMatiere', 'ASC')
            ->getQuery()
            ->getResult();

        $t = [];

        foreach ($req as $r) {
            $t[] = $r->getRessource();
        }

        return $t;
    }
}
