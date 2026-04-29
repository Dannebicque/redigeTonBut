<?php

namespace App\Repository;

use App\Entity\PdfDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PdfDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PdfDocument::class);
    }

    public function findOneByIdentity(string $sourceType, string $sourceId, string $documentKey): ?PdfDocument
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.sourceType = :sourceType')
            ->andWhere('d.sourceId = :sourceId')
            ->andWhere('d.documentKey = :documentKey')
            ->setParameter('sourceType', $sourceType)
            ->setParameter('sourceId', $sourceId)
            ->setParameter('documentKey', $documentKey)
            ->orderBy('d.updatedAt', 'DESC')
            ->addOrderBy('d.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return PdfDocument[]
     */
    public function findAllByIdentity(string $sourceType, string $sourceId, string $documentKey): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.sourceType = :sourceType')
            ->andWhere('d.sourceId = :sourceId')
            ->andWhere('d.documentKey = :documentKey')
            ->setParameter('sourceType', $sourceType)
            ->setParameter('sourceId', $sourceId)
            ->setParameter('documentKey', $documentKey)
            ->orderBy('d.updatedAt', 'DESC')
            ->addOrderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string[] $sourceIds
     *
     * @return PdfDocument[]
     */
    public function findByIdentities(string $sourceType, array $sourceIds, string $documentKey): array
    {
        if (count($sourceIds) === 0) {
            return [];
        }

        $documents = $this->createQueryBuilder('d')
            ->andWhere('d.sourceType = :sourceType')
            ->andWhere('d.documentKey = :documentKey')
            ->andWhere('d.sourceId IN (:sourceIds)')
            ->setParameter('sourceType', $sourceType)
            ->setParameter('documentKey', $documentKey)
            ->setParameter('sourceIds', $sourceIds)
            ->orderBy('d.sourceId', 'ASC')
            ->addOrderBy('d.updatedAt', 'DESC')
            ->addOrderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();

        $latestBySourceId = [];
        foreach ($documents as $document) {
            if (!isset($latestBySourceId[$document->getSourceId()])) {
                $latestBySourceId[$document->getSourceId()] = $document;
            }
        }

        return array_values($latestBySourceId);
    }
}
