<?php

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\ApcSaeApprentissageCritique;
use App\Entity\Departement;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcSaeApprentissageCritique|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcSaeApprentissageCritique|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcSaeApprentissageCritique[]    findAll()
 * @method ApcSaeApprentissageCritique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcSaeApprentissageCritiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcSaeApprentissageCritique::class);
    }

    public function findArrayIdAc($sae)
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.sae = :sae')
            ->setParameter('sae', $sae)
            ->getQuery()
            ->getResult();

        $t = [];
        /** @var ApcSaeApprentissageCritique $q */
        foreach ($query as $q)
        {
            $t[] = $q->getApprentissageCritique()->getId();
        }

        return $t;
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin(ApcSae::class, 'r', 'WITH', 'a.sae = r.id')
            ->innerJoin(Semestre::class, 's', 'WITH', 'r.semestre = s.id')
            ->innerJoin(Annee::class, 'an', 'WITH', 's.annee = an.id')
            ->where('an.departement = :departement')
            ->setParameter('departement', $departement->getId())
            ->getQuery()
            ->getResult();
    }
}
