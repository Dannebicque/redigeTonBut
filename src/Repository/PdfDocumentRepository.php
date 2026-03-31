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
        return $this->findOneBy([
            'sourceType' => $sourceType,
            'sourceId' => $sourceId,
            'documentKey' => $documentKey,
        ]);
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

        return $this->createQueryBuilder('d')
            ->andWhere('d.sourceType = :sourceType')
            ->andWhere('d.documentKey = :documentKey')
            ->andWhere('d.sourceId IN (:sourceIds)')
            ->setParameter('sourceType', $sourceType)
            ->setParameter('documentKey', $documentKey)
            ->setParameter('sourceIds', $sourceIds)
            ->getQuery()
            ->getResult();
    }
}
