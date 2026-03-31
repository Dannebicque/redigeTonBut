<?php

namespace App\Repository;

use App\Entity\PdfJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PdfJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PdfJob::class);
    }

    public function findLatestFor(string $sourceType, string $sourceId, string $documentKey): ?PdfJob
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.sourceType = :sourceType')
            ->andWhere('j.sourceId = :sourceId')
            ->andWhere('j.documentKey = :documentKey')
            ->setParameter('sourceType', $sourceType)
            ->setParameter('sourceId', $sourceId)
            ->setParameter('documentKey', $documentKey)
            ->orderBy('j.requestedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUuid(string $uuid): ?PdfJob
    {
        return $this->find($uuid);
    }

    /**
     * @param string[] $sourceIds
     *
     * @return array<string, PdfJob>
     */
    public function findLatestForSources(string $sourceType, array $sourceIds, string $documentKey): array
    {
        if (count($sourceIds) === 0) {
            return [];
        }

        $jobs = $this->createQueryBuilder('j')
            ->andWhere('j.sourceType = :sourceType')
            ->andWhere('j.documentKey = :documentKey')
            ->andWhere('j.sourceId IN (:sourceIds)')
            ->setParameter('sourceType', $sourceType)
            ->setParameter('documentKey', $documentKey)
            ->setParameter('sourceIds', $sourceIds)
            ->orderBy('j.sourceId', 'ASC')
            ->addOrderBy('j.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $latestBySourceId = [];
        foreach ($jobs as $job) {
            $sourceId = $job->getSourceId();
            if (!isset($latestBySourceId[$sourceId])) {
                $latestBySourceId[$sourceId] = $job;
            }
        }

        return $latestBySourceId;
    }
}
