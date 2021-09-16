<?php

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\ApcSaeParcours;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcSaeParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcSaeParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcSaeParcours[]    findAll()
 * @method ApcSaeParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcSaeParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcSaeParcours::class);
    }

    public function findArrayIdSae(mixed $sae)
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.sae = :sae')
            ->setParameter('sae', $sae)
            ->getQuery()
            ->getResult();

        $t = [];
        foreach ($query as $q) {
            $t[] = $q->getParcours()->getId();
        }

        return $t;
    }

    public function findBySemestre(Semestre $semestre, ApcParcours $parcours)
    {
        $req = $this->createQueryBuilder('p')
            ->innerJoin(ApcSae::class, 's', 'WITH', 'p.sae = s.id')
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
            $t[] = $r->getSae();
        }

        return $t;
    }

    public function findByAnneeArray(Annee $annee, ApcParcours $parcours)
    {

        $query = $this->findByAnnee($annee, $parcours);

        $t = [];
        foreach ($annee->getSemestres() as $semestre)
        {
            $t[$semestre->getOrdreLmd()] = [];
        }

        foreach ($query as $res)
        {
            $t[$res->getSemestre()->getOrdreLmd()][] = $res;
        }

        return $t;
    }

    public function findByAnnee(Annee $annee, ApcParcours $parcours)
    {
        $req = $this->createQueryBuilder('p')
            ->innerJoin(ApcSae::class, 'a', 'WITH', 'p.sae = a.id')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = a.semestre')
            ->where('p.parcours = :parcours')
            ->andWhere('s.annee = :annee')
            ->setParameter('parcours', $parcours->getId())
            ->setParameter('annee', $annee->getId())
            ->orderBy('a.ordre', 'ASC')
            ->addOrderBy('a.codeMatiere', 'ASC')
            ->getQuery()
            ->getResult();

        $t = [];

        foreach ($req as $r) {
            $t[] = $r->getSae();
        }

        return $t;
    }

}
