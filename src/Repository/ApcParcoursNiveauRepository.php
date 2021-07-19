<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcParcoursNiveauRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/03/2021 17:33
 */

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcCompetence;
use App\Entity\ApcNiveau;
use App\Entity\ApcParcours;
use App\Entity\ApcParcoursNiveau;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcParcoursNiveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcParcoursNiveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcParcoursNiveau[]    findAll()
 * @method ApcParcoursNiveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcParcoursNiveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcParcoursNiveau::class);
    }

    public function findParcoursNiveau(ApcParcours $parcours, ApcNiveau $niveau)
    {
        return $this->createQueryBuilder('p')
            ->where('p.niveau = :niveau')
            ->andWhere('p.parcours = :parcours')
            ->setParameter('niveau', $niveau->getid())
            ->setParameter('parcours', $parcours->getid())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNiveauByParcoursArray(ApcParcours $parcours)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.parcours = :parcours')
            ->setParameter('parcours', $parcours->getid())
            ->getQuery()
            ->getResult();

        $t = [];
        /** @var ApcParcoursNiveau $q */
        foreach ($query as $q) {
            if ($q->getNiveau() !== null) {
                $t[$q->getNiveau()->getId()] = $q;
            }
        }

        return $t;
    }

    public function findBySemestre(Semestre $semestre, ApcParcours $parcours)
    {
        $n = $this->createQueryBuilder('n')
            ->innerJoin(ApcNiveau::class, 'niv', 'WITH', 'n.niveau = niv.id')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'niv.competence = c.id')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = niv.annee')
            ->where('a.id = :annee')
            ->andWhere('n.parcours = :parcour')
            ->setParameter('annee', $semestre->getAnnee()->getId())
            ->setParameter('parcour', $parcours->getId())
            ->orderBy('c.couleur', 'ASC')
            ->getQuery()
            ->getResult();

        $t = [];
        foreach ($n as $np) {
            $t[] = $np->getNiveau();
        }

        return $t;
    }

    public function findParcoursNiveauCompetence(mixed $parcours)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'p.niveau = n.id')
            ->innerJoin(ApcParcours::class, 'ap', 'WITH', 'p.parcours = ap.id')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'n.competence = c.id')
            ->where('ap.id = :parcours')
            ->setParameter('parcours', $parcours)
            ->orderBy('n.ordre', 'ASC')
            ->addOrderBy('c.couleur', 'ASC')
            ->getQuery()
            ->getResult();

    }

    public function findParcoursSemestreCompetence(Semestre $semestre, ApcParcours $apcParcours)
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'p.niveau = n.id')
            ->innerJoin(ApcParcours::class, 'ap', 'WITH', 'p.parcours = ap.id')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'n.competence = c.id')
            ->where('ap.id = :parcours')
            ->andWhere('n.annee = :annee')
            ->setParameter('parcours', $apcParcours)
            ->setParameter('annee', $semestre->getAnnee()->getId())
            ->orderBy('n.ordre', 'ASC')
            ->addOrderBy('c.couleur', 'ASC')
            ->getQuery()
            ->getResult();

        $t = [];
        /** @var ApcParcoursNiveau $q */
        foreach ($query as $q) {
            $t[] = $q->getNiveau()->getCompetence();
        }

        return $t;
    }
}
