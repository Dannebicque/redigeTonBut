<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcParcoursRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:08
 */

namespace App\Repository;

use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Semestre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcParcours|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcParcours|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcParcours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcParcours::class);
    }

    public function findAll()
    {
        return $this->findBy([], ['ordre' => 'ASC', 'libelle' => 'ASC']);
    }

    public function findOneByDepartementArray(Departement $departement)
    {
        $t = [];
        foreach ($departement->getApcParcours() as $parcours)
        {
            $t[$parcours->getCode()] = $parcours;
        }

        return $t;
    }
}
