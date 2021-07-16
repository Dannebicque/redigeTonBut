<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcComptenceRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 16:47
 */

namespace App\Repository;

use App\Entity\ApcCompetence;
use App\Entity\Departement;
use App\Entity\Diplome;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcCompetence|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcCompetence|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcCompetence[]    findAll()
 * @method ApcCompetence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcComptenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcCompetence::class);
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->findByDepartementBuilder($departement)
            ->getQuery()
            ->getResult();
    }

    public function findByDepartementBuilder(Departement $departement)
    {
        return $this->createQueryBuilder('c')
            ->where('c.departement = :departement')
            ->setParameter('departement', $departement->getId())
            ->orderBy('c.couleur', 'ASC')
            ;
    }

    public function findOneByDepartementArray(Departement $departement): array
    {
        $comps = $this->findByDepartement($departement);
        $t = [];
        foreach ($comps as $c) {
            $t[$c->getNomCourt()] = $c;
        }

        return $t;
    }

    public function findOther(string $ordreDestination, ApcCompetence $competence)
    {
        return $this->createQueryBuilder('a')
            ->where('a.couleur = :couleur')
            ->andWhere('a.departement = :departement')
            ->andWhere('a.id != :id')
            ->setParameter('couleur', $ordreDestination)
            ->setParameter('departement', $competence->getDepartement()->getId())
            ->setParameter('id', $competence->getId())
            ->getQuery()
            ->getResult();
    }
}
