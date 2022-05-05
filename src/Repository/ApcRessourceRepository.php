<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcRessourceRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 27/05/2021 06:38
 */

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\ApcRessource;
use App\Entity\ApcRessourceParcours;
use App\Entity\Departement;
use App\Entity\Diplome;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcRessource|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcRessource|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcRessource[]    findAll()
 * @method ApcRessource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcRessourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcRessource::class);
    }

    public function findBySemestre(Semestre $semestre)
    {
        return $this->getBySemestre($semestre, false);
    }

    public function findBySemestreAl(Semestre $semestre)
    {
        return $this->getBySemestre($semestre, true);
    }

    public function search(?string $search)
    {
        return $this->createQueryBuilder('a')
            ->where('a.libelle LIKE :search')
            ->orWhere('a.description LIKE :search')
            ->orWhere('a.motsCles LIKE :search')
            ->orWhere('a.libelle LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->where('a.departement = :departement')
            ->andWhere('r.ficheAdaptationLocale = false')
            ->setParameter('departement', $departement->getId())
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAnneeArray(Annee $annee)
    {
        $query = $this->findByAnnee($annee);

        $t = [];
        foreach ($annee->getSemestres() as $semestre) {
            $t[$semestre->getOrdreLmd()] = [];
        }

        foreach ($query as $res) {
            $t[$res->getSemestre()->getOrdreLmd()][] = $res;
        }

        return $t;
    }

    public function findByAnnee(Annee $annee)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->where('s.annee = :annee')
            // ->andWhere('r.ficheAdaptationLocale = false')
            ->setParameter('annee', $annee->getId())
            ->orderBy('r.semestre', 'ASC')
            ->addOrderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySemestreEtPrecedent(Semestre $semestre, array $semestres)
    {
        $query = $this->createQueryBuilder('r')
            ->orderBy('r.semestre', 'ASC')
            ->addOrderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC');

        $i = 0;
        //todo: ne filtre pas selon les parcours...
        foreach ($semestres as $sem) {
            if ($sem->getOrdreLmd() <= $semestre->getOrdreLmd()) {
                $query->orWhere('r.semestre = :semestre' . $i)
                    ->setParameter('semestre' . $i, $sem->getId());
                $i++;
            }
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findOrdreMax(Semestre $semestre)
    {
        return $this->createQueryBuilder('r')
            ->select('MAX(r.ordre) as ordreMax')
            ->where('r.semestre = :semestre')
            ->setParameter('semestre', $semestre->getId())
            ->getQuery()
            ->getScalarResult();
    }


    public function findByDepartementArray(Departement $departement)
    {
        $ressources = $this->findByDepartement($departement);
        $tab = [];
        foreach ($ressources as $res) {
            $tab[$res->getCodeMatiere()] = $res;
        }

        return $tab;
    }


    private function getBySemestre(Semestre $semestre, bool $al): mixed
    {
        return $this->createQueryBuilder('r')
            ->where('r.semestre = :semestre')
            ->andWhere('r.ficheAdaptationLocale = :al')
            ->setParameter('semestre', $semestre->getId())
            ->setParameter('al', $al)
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPPP()
    {
        return $this->createQueryBuilder('res')
            ->where('res.libelle LIKE :t1')
            ->andWhere('res.libelle LIKE :t2')
            ->andWhere('res.libelle LIKE :t3')
            ->orWhere('res.libelle LIKE :t4')
            ->setParameter('t1', '%rofessionnel%')
            ->setParameter('t2', '%rojet%')
            ->setParameter('t3', '%ersonnel%')
            ->setParameter('t4', '%PPP%')
            ->getQuery()
            ->getResult();
    }

    public function findBySemestreArray(Semestre $semestre)
    {
        return $this->createQueryBuilder('r')
            ->where('r.semestre = :semestre')
            ->andWhere('r.ficheAdaptationLocale = false')
            ->setParameter('semestre', $semestre->getId())
            ->addOrderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDD(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.libelle LIKE :t1')
            ->orWhere('r.description LIKE :t1')
            ->orWhere('r.motsCles LIKE :t1')
            ->orwhere('r.libelle LIKE :t2')
            ->orWhere('r.description LIKE :t2')
            ->orWhere('r.motsCles LIKE :t2')
            ->setParameter('t1', '%urable%')
            ->setParameter('t2', '%éco-%')
            ->getQuery()
            ->getResult();
    }

    public function findByKeywords(array $keywords): array
    {
        $query = $this->createQueryBuilder('r');

        foreach ($keywords as $keyword) {
            $query->orWhere('r.libelle LIKE :t1')
                ->orWhere('r.description LIKE :t1')
                ->orWhere('r.motsCles LIKE :t1')
                ->setParameter('t1', mb_strtolower($keyword));
        }

        return $query->getQuery()
            ->getResult();
    }
}
