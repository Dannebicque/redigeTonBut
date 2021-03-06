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
        return $this->getBySemestre($parcours, $semestre, false);
    }

    public function findBySemestreAl(Semestre $semestre, ApcParcours $parcours)
    {
        return $this->getBySemestre($parcours, $semestre, true);
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
           // ->andWhere('a.ficheAdaptationLocale = false')
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

    public function findSaeWithParcours(int $id)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin(ApcSae::class, 'a', 'WITH', 's.sae = a.id')
            ->where('a.id = :sae')
            ->setParameter('sae', $id)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \App\Entity\ApcParcours $parcours
     * @param \App\Entity\Semestre    $semestre
     *
     * @return array
     */
    private function getBySemestre(ApcParcours $parcours, Semestre $semestre, bool $al): array
    {
        $req = $this->createQueryBuilder('p')
            ->innerJoin(ApcSae::class, 's', 'WITH', 'p.sae = s.id')
            ->innerJoin(Semestre::class, 'se', 'WITH', 's.semestre = se.id')
            ->where('p.parcours = :parcours')
            ->andWhere('se.ordreLmd = :semestre')
            ->andWhere('s.ficheAdaptationLocale = :al')
            ->setParameter('parcours', $parcours->getId())
            ->setParameter('al', $al)
            ->setParameter('semestre', $semestre->getOrdreLmd())
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

    public function findBySemestreArray(Semestre $semestre, ApcParcours $parcours)
    {
        $req = $this->createQueryBuilder('p')
            ->innerJoin(ApcSae::class, 'a', 'WITH', 'p.sae = a.id')
            ->where('p.parcours = :parcours')
            ->andWhere('a.ficheAdaptationLocale = false')
            ->andWhere('a.semestre = :semestre')
            ->setParameter('parcours', $parcours->getId())
            ->setParameter('semestre', $semestre->getId())
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
