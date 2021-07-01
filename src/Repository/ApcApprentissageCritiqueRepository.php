<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcApprentissageCritiqueRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 17:04
 */

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcApprentissageCritique;
use App\Entity\ApcCompetence;
use App\Entity\ApcNiveau;
use App\Entity\Departement;
use App\Entity\Diplome;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcApprentissageCritique|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcApprentissageCritique|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcApprentissageCritique[]    findAll()
 * @method ApcApprentissageCritique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcApprentissageCritiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcApprentissageCritique::class);
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->findByDepartementBuilder($departement)
            ->getQuery()
            ->getResult();
    }

    public function findByDepartementBuilder(Departement $departement)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'a.niveau = n.id')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'c.id = n.competence')
            ->where('c.departement = :departement')
            ->setParameter('departement', $departement->getId());
    }

    public function findBySemestreAndCompetences(
        Annee $annee,
        $idCompetences
    ) {
        $query = $this->createQueryBuilder('a')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'a.niveau = n.id')
            ->where('n.annee = :annee')
            ->setParameter('annee', $annee->getId());

        $ors = [];
        foreach ($idCompetences as $comp) {
            $ors[] = $query->expr()->orx('n.competence = ' . $query->expr()->literal($comp['id']));
        }

        return $query->andWhere(implode(' OR ', $ors))
            ->getQuery()
            ->getResult();
    }

    public function findOneByDepartementArray(Departement $departement): array
    {
        $comps = $this->findByDepartement($departement);
        $t = [];
        foreach ($comps as $c) {
            $t[$c->getCode()] = $c;
        }

        return $t;
    }
}
