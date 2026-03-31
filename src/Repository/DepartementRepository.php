<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/DepartementRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:08
 */

namespace App\Repository;

use App\Entity\Departement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Departement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Departement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Departement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartementRepository extends ServiceEntityRepository
{
    /**
     * DepartementRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Departement::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['sigle' => 'ASC']);
    }

    public function findBySiteIut(int $iut)
    {
        return $this->createQueryBuilder('departement')
            ->innerJoin('departement.apcParcours', 'apc_parcours')
            ->innerJoin('apc_parcours.iutSiteParcours', 'iut_site_parcours')
            ->where('iut_site_parcours.site = :iut')
            ->setParameter('iut', $iut)
            ->groupBy('departement.id')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(?UserInterface $user)
    {
        if ($user === null) {
            return [];
        }

        // tester si l'utilisateur est pacd du département
        if ($user->ispacd()) {
            return [$user->getDepartement()];
        }

        $qb = $this->createquerybuilder('departement') //todo: ajouter le pacd
            ->andwhere(':user member of departement.cpns')
            ->setparameter('user', $user);


        return $qb->getquery()->getresult();
    }

    public function findByActifs() : array
    {
        return $this->createQueryBuilder('departement')
            ->innerJoin('departement.versions', 'version')
            ->where('version.actif = :actif')
            ->setParameter('actif', true)
            ->getQuery()
            ->getResult();
    }

    public function findByVersion(int $annee): array
    {
        return $this->createQueryBuilder('departement')
            ->innerJoin('departement.versions', 'version')
            ->where('version.annee = :annee')
            ->setParameter('annee', $annee)
            ->getQuery()
            ->getResult();
    }
}
