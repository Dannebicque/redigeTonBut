<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcComposanteEssentielleRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 07/02/2021 11:08
 */

namespace App\Repository;

use App\Entity\ApcComposanteEssentielle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcComposanteEssentielle|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcComposanteEssentielle|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcComposanteEssentielle[]    findAll()
 * @method ApcComposanteEssentielle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcComposanteEssentielleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcComposanteEssentielle::class);
    }

    public function findOther(?int $ordreDestination, ApcComposanteEssentielle $apcComposanteEssentielle)
    {
        return $this->createQueryBuilder('a')
            ->where('a.ordre = :ordre')
            ->andWhere('a.competence = :competence')
            ->andWhere('a.id != :id')
            ->setParameter('ordre', $ordreDestination)
            ->setParameter('competence', $apcComposanteEssentielle->getCompetence()?->getId())
            ->setParameter('id', $apcComposanteEssentielle->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
