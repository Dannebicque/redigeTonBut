<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcRessourceCompetenceRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 01/03/2021 18:49
 */

namespace App\Repository;

use App\Entity\ApcRessource;
use App\Entity\ApcRessourceCompetence;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcRessourceCompetence|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcRessourceCompetence|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcRessourceCompetence[]    findAll()
 * @method ApcRessourceCompetence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcRessourceCompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcRessourceCompetence::class);
    }

    public function findBySemestre(Semestre $semestre)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin(ApcRessource::class, 's', 'WITH', 'c.ressource = s.id')
            ->where('s.semestre = :semestre')
            ->setParameter('semestre', $semestre->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByRessourceArray($ressource)
    {
        $res = $this->findBy(['ressource' => $ressource]);
        $t = [];

        foreach ($res as $r) {
            $t[] = $r->getCompetence()->getId();
        }

        return $t;
    }

}
