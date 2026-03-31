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
use App\Entity\Semestre;
use App\Entity\Version;
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

    public function findByVersion(Version $version)
    {
        return $this->findByVersionBuilder($version)
            ->getQuery()
            ->getResult();
    }

    public function findByVersionBuilder(Version $version): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'a.niveau = n.id')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'c.id = n.competence')
            ->where('c.version = :version')
            ->orderBy('c.couleur', 'ASC')
            ->addOrderBy('n.ordre', 'ASC')
            ->addOrderBy('a.ordre', 'ASC')
            ->addOrderBy('a.code', 'ASC')
            ->setParameter('version', $version->getId());
    }

    public function findBySemestreAndCompetences(
        Annee $annee,
        $idCompetences
    ) {
        $query = $this->createQueryBuilder('a')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'a.niveau = n.id')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'n.competence = c.id')
            ->where('n.annee = :annee')
            ->setParameter('annee', $annee->getId());

        $ors = [];
        foreach ($idCompetences as $comp) {
            $ors[] = $query->expr()->orx('n.competence = ' . $query->expr()->literal($comp));
        }

        return $query->andWhere(implode(' OR ', $ors))
            ->orderBy('c.couleur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySemestre(
        Semestre $semestre
    ) {
        $query = $this->createQueryBuilder('a')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'a.niveau = n.id')
            ->innerJoin(ApcCompetence::class, 'c', 'WITH', 'n.competence = c.id')
            ->where('n.annee = :annee')
            ->setParameter('annee', $semestre->getAnnee()->getId());


        return $query
            ->orderBy('c.couleur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByVersionArray(Version $version): array
    {
        $comps = $this->findByVersion($version, $version->getDepartement());
        $t = [];
        foreach ($comps as $c) {
            $t[$c->getCode()] = $c;
        }

        return $t;
    }

    public function findOther(?int $ordreDestination, ApcApprentissageCritique $apcApprentissageCritique)
    {
        return $this->createQueryBuilder('a')
            ->where('a.ordre = :ordre')
            ->andWhere('a.niveau = :niveau')
            ->andWhere('a.id != :id')
            ->setParameter('ordre', $ordreDestination)
            ->setParameter('niveau', $apcApprentissageCritique->getNiveau()->getId())
            ->setParameter('id', $apcApprentissageCritique->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCompetence(ApcCompetence $competence)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin(ApcNiveau::class, 'n', 'WITH', 'a.niveau = n.id')
            ->innerJoin(Annee::class, 'an', 'WITH', 'n.annee = an.id')
            ->where('an.version = :version')
            ->setParameter('version', $competence->getVersion()->getId())
            ->getQuery()
            ->getResult();
    }
}
