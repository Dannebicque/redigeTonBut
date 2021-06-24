<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/SemestreRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 16:35
 */

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\Departement;
use App\Entity\Diplome;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Semestre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Semestre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Semestre[]    findAll()
 * @method Semestre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SemestreRepository extends ServiceEntityRepository
{
    /**
     * SemestreRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Semestre::class);
    }

    public function findByDepartementBuilder(Departement $departement): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->where('a.departement = :departement')
            ->setParameter('departement', $departement->getId())
            ->orderBy('s.ordreLmd', 'ASC')
            ->addOrderBy('s.libelle', 'ASC');
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->findByDepartementBuilder($departement)->getQuery()->getResult();
    }

    public function findByDepartementArray(Departement $departement)
    {
        $semestres = $this->findByDepartement($departement);

        $tabsemestre = [];

        /** @var Semestre $semestre */
        foreach ($semestres as $semestre) {
            $tabsemestre[$semestre->getOrdreLmd()] = $semestre;
        }

        return $tabsemestre;
    }

    public function findOneByDepartementEtNumero(Departement $departement, string $numero, string $ordreAnnee)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->where('a.departement = :departement')
            ->andWhere('s.ordreAnnee = :ordreAnnee')
            ->andWhere('s.ordreLmd = :numero')
            ->setParameter('departement', $departement->getId())
            ->setParameter('numero', $numero)
            ->setParameter('ordreAnnee', $ordreAnnee)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findSemestre(
        Departement $departement,
        int $semestre
    ): ?Semestre {
        return $this->createQueryBuilder('s')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->where('a.departement = :departement')
            ->andWhere('s.ordreLmd = :numero')
            ->setParameter('departement', $departement->getId())
            ->setParameter('numero', $semestre)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
