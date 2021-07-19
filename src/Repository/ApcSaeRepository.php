<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcSaeRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 27/05/2021 06:38
 */

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\ApcSae;
use App\Entity\ApcSaeParcours;
use App\Entity\Departement;
use App\Entity\Diplome;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcSae|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcSae|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcSae[]    findAll()
 * @method ApcSae[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcSaeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcSae::class);
    }

    public function findBySemestre(Semestre $semestre)
    {
        return $this->createQueryBuilder('r')
            ->where('r.semestre = :semestre')
            ->setParameter('semestre', $semestre->getId())
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function search(?string $search)
    {
        return $this->createQueryBuilder('a')
            ->where('a.libelle LIKE :search')
            ->orWhere('a.livrables LIKE :search')
            ->orWhere('a.description LIKE :search')
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
            ->setParameter('departement', $departement->getId())
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDepartementToSemestreArray(?Departement $departement)
    {
        $tab = [];
        foreach ($departement->getSemestres() as $semestre) {
            $tab[$semestre->getId()] = $this->findBySemestre($semestre);
        }

        return $tab;
    }

    public function findByAnneeArray(Annee $annee)
    {
        $query = $this->findByAnnee($annee);

        $t = [];
        foreach ($annee->getSemestres() as $semestre)
        {
            $t[$semestre->getId()] = [];
        }

        foreach ($query as $res)
        {
            $t[$res->getSemestre()->getId()][] = $res;
        }

        return $t;
    }

    public function findByAnnee(Annee $annee)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->where('s.annee = :annee')
            ->setParameter('annee', $annee->getId())
            ->orderBy('r.semestre', 'ASC')
            ->addOrderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
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

    public function findBySemestreAndParcours(mixed $semestre, ?ApcParcours $apcParcours)
    {
        if ($apcParcours !== null) {
            return $this->createQueryBuilder('r')
                ->innerJoin(ApcSaeParcours::class, 'p', 'WITH', 'r.id = p.sae')
                ->where('r.semestre = :semestre')
                ->andWhere('p.parcours = :parcours')
                ->setParameter('semestre', $semestre->getId())
                ->setParameter('parcours', $apcParcours->getId())
                ->orderBy('r.ordre', 'ASC')
                ->addOrderBy('r.codeMatiere', 'ASC')
                ->addOrderBy('r.libelle', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->findBySemestre($semestre);
    }
}
