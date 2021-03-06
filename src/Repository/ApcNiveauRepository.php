<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcNiveauRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/06/2021 17:40
 */

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcCompetence;
use App\Entity\ApcNiveau;
use App\Entity\ApcParcours;
use App\Entity\ApcParcoursNiveau;
use App\Entity\Departement;
use App\Entity\Diplome;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcNiveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcNiveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcNiveau[]    findAll()
 * @method ApcNiveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcNiveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcNiveau::class);
    }

    public function findBySemestre(Semestre $semestre)
    {
        $query = $this->createQueryBuilder('n')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'n.competence = c.id')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = n.annee')
            ->where('a.id = :annee')
            ->setParameter('annee', $semestre->getAnnee()->getId())
            ->orderBy('n.ordre', 'ASC')
            ->addOrderBy('c.couleur', 'ASC');

        if ($semestre->getApcParcours() !== null) {
            $query->innerJoin(ApcParcoursNiveau::class, 'pn', 'WITH', 'n.id = pn.niveau')
                ->andWhere('pn.parcours = :parcours')
                ->setParameter('parcours', $semestre->getApcParcours()->getId());
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findByDepartement(Departement $departement)
    {
        return $this->createQueryBuilder('n')
            ->innerJoin(ApcCompetence::class, 'a', 'WITH', 'a.id = n.competence')
            ->where('a.departement = :departement')
            ->setParameter('departement', $departement->getId())
            ->getQuery()
            ->getResult();
    }

    public function findBySemestreArrayCompetence(Semestre $semestre)
    {
        $query = $this->findBySemestre($semestre);

        $t = [];

        foreach ($query as $q) {
            $t[] = $q->getCompetence();
        }

        return $t;
    }
}
