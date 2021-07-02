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
use App\Entity\ApcRessource;
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

//    public function findByDiplome(Diplome $diplome)
//    {
//        return $this->createQueryBuilder('r')
//            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
//            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
//            ->where('a.diplome = :diplome')
//            //->andWhere('s.ppn_actif = m.ppn')
//            ->setParameter('diplome', $diplome->getId())
//            ->orderBy('r.codeMatiere', 'ASC')
//            ->addOrderBy('r.libelle', 'ASC')
//            ->getQuery()
//            ->getResult();
//    }

    public function findBySemestre(Semestre $semestre)
    {
        return $this->createQueryBuilder('r')
            ->where('r.semestre = :semestre')
            //->andWhere('s.ppn_actif = m.ppn')
            ->setParameter('semestre', $semestre->getId())
            ->orderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
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
            //->andWhere('s.ppn_actif = m.ppn')
            ->setParameter('departement', $departement->getId())
            ->orderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    public function findByDiplomeToSemestreArray(Diplome $diplome)
//    {
//        $tab = [];
//        foreach ($diplome->getSemestres() as $semestre) {
//            $tab[$semestre->getId()] = $this->findBySemestre($semestre);
//        }
//
//        return $tab;
//    }

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
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
