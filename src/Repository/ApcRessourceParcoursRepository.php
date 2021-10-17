<?php

namespace App\Repository;

use App\Entity\Annee;
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
            $t[] = $q->getParcours()->getId();
        }

        return $t;

    }

    public function findBySemestre(Semestre $semestre, ApcParcours $parcours)
    {
        $req = $this->createQueryBuilder('p')
            ->innerJoin(ApcRessource::class, 's', 'WITH', 'p.ressource = s.id')
            ->innerJoin(Semestre::class, 'se', 'WITH', 's.semestre = se.id')
            ->where('p.parcours = :parcours')
            ->andWhere('se.ordreLmd = :semestre')
            ->setParameter('parcours', $parcours->getId())
            ->setParameter('semestre', $semestre->getOrdreLmd())
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

    public function findByAnnee(Annee $annee, ApcParcours $parcours)
    {
        $req = $this->createQueryBuilder('p')
            ->innerJoin(ApcRessource::class, 'a', 'WITH', 'p.ressource = a.id')
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
            $t[] = $r->getRessource();
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

    public function findBySemestreEtPrecedent(Semestre $semestre, ApcParcours $parcours, array $semestres)
    {
        $query = $this->createQueryBuilder('r')
            ->innerJoin(ApcRessource::class, 'a', 'WITH', 'r.ressource = a.id')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = a.semestre')

            ->orderBy('a.codeMatiere', 'ASC')
            ->addOrderBy('a.semestre', 'ASC')
            ->addOrderBy('a.libelle', 'ASC');

        $i = 0;
        foreach ($semestres as $sem) {
            if ($sem->getOrdreLmd() <= $semestre->getOrdreLmd()) {
                $query->orWhere('a.semestre = :semestre'.$i)
                    ->setParameter('semestre'.$i, $sem->getId());
                $i++;
            }
        }

        $query->andWhere('r.parcours = :parcours')
            ->setParameter('parcours', $parcours->getId());

        return $query->getQuery()->getResult();
    }
}
