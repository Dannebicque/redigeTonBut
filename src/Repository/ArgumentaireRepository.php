<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Argumentaire;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Argumentaire>
 */
class ArgumentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Argumentaire::class);
    }

    public function findOneByVersion(Version $version): ?Argumentaire
    {
        return $this->findOneBy(['version' => $version]);
    }
}

