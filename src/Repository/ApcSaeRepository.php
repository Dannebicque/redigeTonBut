<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Repository/ApcSaeRepository.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 27/05/2021 06:38
 */

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\ApcSae;
use App\Entity\Semestre;
use App\Entity\Version;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApcSae|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcSae|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcSae[]    findAll()
 * @method ApcSae[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcSaeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcSae::class);
    }

    public function findBySemestre(Semestre $semestre): mixed
    {
        return $this->getBySemestre($semestre, false);
    }

    public function findBySemestreAL(Semestre $semestre): mixed
    {
        return $this->getBySemestre($semestre, true);
    }

    public function search(?string $search)
    {
        return $this->createQueryBuilder('a')
            ->where('a.libelle LIKE :search')
            ->orWhere('a.livrables LIKE :search')
            ->orWhere('a.description LIKE :search')
            ->orWhere('a.libelle LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    public function findByVersion(Version $version)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->where('a.version = :version')
            ->andWhere('r.ficheAdaptationLocale = false')
            ->setParameter('version', $version->getId())
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByVersionAl(Version $version)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->where('a.version = :version')
            ->andWhere('r.ficheAdaptationLocale = true')
            ->setParameter('version', $version->getId())
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByVersionArray(?Version $version): array
    {
        $saes = $this->findByVersion($version);
        $tab = [];
        foreach ($saes as $sae) {
            $tab[$sae->getCodeMatiere()] = $sae;
        }

        return $tab;
    }

    /**
     * @return mixed[][]
     */
    public function findByAnneeArray(Annee $annee): array
    {
        $query = $this->findByAnnee($annee);

        $t = [];
        foreach ($annee->getSemestres() as $semestre) {
            $t[$semestre->getOrdreLmd()] = [];
        }

        foreach ($query as $res) {
            $t[$res->getSemestre()->getOrdreLmd()][] = $res;
        }

        return $t;
    }

    public function findByAnnee(Annee $annee)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->where('s.annee = :annee')
            // ->andWhere('r.ficheAdaptationLocale = false')
            ->setParameter('annee', $annee->getId())
            ->orderBy('r.semestre', 'ASC')
            ->addOrderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOrdreMax(Semestre $semestre): array|float|int|string
    {
        return $this->createQueryBuilder('r')
            ->select('MAX(r.ordre) as ordreMax')
            ->where('r.semestre = :semestre')
            ->setParameter('semestre', $semestre->getId())
            ->getQuery()
            ->getScalarResult();
    }

    private function getBySemestre(Semestre $semestre, bool $al): mixed
    {
        return $this->createQueryBuilder('r')
            ->where('r.semestre = :semestre')
            ->andWhere('r.ficheAdaptationLocale = :al')
            ->setParameter('al', $al)
            ->setParameter('semestre', $semestre->getId())
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySemestreArray(Semestre $semestre)
    {
        return $this->createQueryBuilder('r')
            ->where('r.semestre = :semestre')
            ->andWhere('r.ficheAdaptationLocale = false')
            ->setParameter('semestre', $semestre->getId())
            ->orderBy('r.semestre', 'ASC')
            ->addOrderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDD(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.libelle LIKE :t1')
            ->orWhere('r.objectifs LIKE :t1')
            ->orWhere('r.description LIKE :t1')
            ->orwhere('r.libelle LIKE :t2')
            ->orWhere('r.objectifs LIKE :t2')
            ->orWhere('r.description LIKE :t2')
            ->setParameter('t1', '%urable%')
            ->setParameter('t2', '%éco-%')
            ->getQuery()
            ->getResult();
    }

    public function findByKeywords(array $keyWords)
    {
        $query = $this->createQueryBuilder('r');

        foreach ($keyWords as $keyword) {
            $query->orWhere('r.libelle LIKE :t1')
                ->orWhere('r.description LIKE :t1')
                ->orWhere('r.objectifs LIKE :t1')
                ->setParameter('t1', '%'.mb_strtolower($keyword).'%');
        }

        return $query->getQuery()
            ->getResult();
    }

    public function findByParcours(int $iut)
    {
        return $this->createQueryBuilder('r')
            ->join('r.apcSaeParcours', 'p')

            ->where('p.parcours = :iut')

            ->setParameter('iut', $iut)
            ->orderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }


    //todo: gérer la version
    public function findBySigleVersion(string $sigle)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->innerJoin('a.version', 'v')
            ->innerJoin('v.departement', 'd')
            ->where('d.sigle = :departement')
            ->andWhere('r.ficheAdaptationLocale = false')
            ->setParameter('departement', $sigle)
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTroncCommun(Version $version)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(Semestre::class, 's', 'WITH', 's.id = r.semestre')
            ->innerJoin(Annee::class, 'a', 'WITH', 'a.id = s.annee')
            ->where('a.version = :version')
            ->andWhere('r.ficheAdaptationLocale = false')
            ->andWhere('r.apcSaeParcours IS EMPTY')
            ->setParameter('version', $version->getId())
            ->orderBy('r.ordre', 'ASC')
            ->addOrderBy('r.codeMatiere', 'ASC')
            ->addOrderBy('r.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
