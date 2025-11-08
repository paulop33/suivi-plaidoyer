<?php

namespace App\Repository;

use App\Entity\ProgressUpdate;
use App\Entity\ElectedList;
use App\Entity\City;
use App\Entity\Commitment;
use App\Enum\ImplementationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProgressUpdate>
 */
class ProgressUpdateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgressUpdate::class);
    }

    /**
     * Trouve les dernières mises à jour pour une liste élue
     */
    public function findLatestByElectedList(ElectedList $electedList, int $limit = 10): array
    {
        return $this->createQueryBuilder('pu')
            ->leftJoin('pu.commitment', 'c')
            ->leftJoin('c.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->leftJoin('pu.updatedBy', 'u')
            ->addSelect('c', 'p', 'cat', 'u')
            ->andWhere('pu.electedList = :electedList')
            ->setParameter('electedList', $electedList)
            ->orderBy('pu.updateDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve la dernière mise à jour pour chaque engagement d'une liste élue
     */
    public function findLatestByCommitmentForElectedList(ElectedList $electedList): array
    {
        $subQuery = $this->createQueryBuilder('pu2')
            ->select('MAX(pu2.updateDate)')
            ->andWhere('pu2.commitment = pu.commitment')
            ->andWhere('pu2.electedList = pu.electedList')
            ->getDQL();

        return $this->createQueryBuilder('pu')
            ->leftJoin('pu.commitment', 'c')
            ->leftJoin('c.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->leftJoin('pu.updatedBy', 'u')
            ->addSelect('c', 'p', 'cat', 'u')
            ->andWhere('pu.electedList = :electedList')
            ->andWhere('pu.updateDate = (' . $subQuery . ')')
            ->setParameter('electedList', $electedList)
            ->orderBy('cat.position', 'ASC')
            ->addOrderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques de progression pour une ville
     */
    public function getCityProgressStats(City $city): array
    {
        $electedList = $this->getEntityManager()
            ->getRepository(ElectedList::class)
            ->findByCity($city);

        if (!$electedList) {
            return [];
        }

        $qb = $this->createQueryBuilder('pu');

        // Compter par statut
        $statusCounts = $qb->select('pu.status as status, COUNT(pu.id) as count')
            ->andWhere('pu.electedList = :electedList')
            ->setParameter('electedList', $electedList)
            ->groupBy('pu.status')
            ->getQuery()
            ->getResult();

        // Progression moyenne
        $avgProgress = $qb->select('AVG(pu.progressPercentage) as avgProgress')
            ->andWhere('pu.electedList = :electedList')
            ->andWhere('pu.progressPercentage IS NOT NULL')
            ->setParameter('electedList', $electedList)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'statusCounts' => $statusCounts,
            'averageProgress' => $avgProgress ? round($avgProgress, 1) : 0,
        ];
    }

    /**
     * Trouve les mises à jour par statut
     */
    public function findByStatus(ImplementationStatus $status, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('pu')
            ->leftJoin('pu.commitment', 'c')
            ->leftJoin('c.proposition', 'p')
            ->leftJoin('pu.electedList', 'el')
            ->leftJoin('el.city', 'city')
            ->addSelect('c', 'p', 'el', 'city')
            ->andWhere('pu.status = :status')
            ->setParameter('status', $status)
            ->orderBy('pu.updateDate', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les mises à jour récentes (toutes villes confondues)
     */
    public function findRecentUpdates(int $limit = 20): array
    {
        return $this->createQueryBuilder('pu')
            ->leftJoin('pu.commitment', 'c')
            ->leftJoin('c.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->leftJoin('pu.electedList', 'el')
            ->leftJoin('el.city', 'city')
            ->leftJoin('pu.updatedBy', 'u')
            ->addSelect('c', 'p', 'cat', 'el', 'city', 'u')
            ->orderBy('pu.updateDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques globales de progression
     */
    public function getGlobalProgressStats(): array
    {
        try {
            $qb = $this->createQueryBuilder('pu');

            // Total des mises à jour
            $totalUpdates = $qb->select('COUNT(pu.id)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            // Répartition par statut
            $statusDistribution = $qb->select('pu.status as status, COUNT(pu.id) as count')
                ->groupBy('pu.status')
                ->getQuery()
                ->getResult();

            // Progression moyenne globale
            $avgProgress = null;
            try {
                $avgProgress = $qb->select('AVG(pu.progressPercentage) as avgProgress')
                    ->andWhere('pu.progressPercentage IS NOT NULL')
                    ->getQuery()
                    ->getSingleScalarResult();
            } catch (\Exception $e) {
                $avgProgress = null;
            }



            return [
                'totalUpdates' => (int) $totalUpdates,
                'statusDistribution' => $statusDistribution,
                'averageProgress' => $avgProgress ? round($avgProgress, 1) : 0,
            ];
        } catch (\Exception $e) {
            // Retourner des valeurs par défaut si aucune donnée n'existe
            return [
                'totalUpdates' => 0,
                'statusDistribution' => [],
                'averageProgress' => 0,
            ];
        }
    }

    /**
     * Trouve les engagements sans mise à jour récente
     */
    public function findStaleCommitments(int $daysThreshold = 90): array
    {
        $thresholdDate = new \DateTime('-' . $daysThreshold . ' days');

        return $this->createQueryBuilder('pu')
            ->leftJoin('pu.commitment', 'c')
            ->leftJoin('c.proposition', 'p')
            ->leftJoin('pu.electedList', 'el')
            ->leftJoin('el.city', 'city')
            ->addSelect('c', 'p', 'el', 'city')
            ->andWhere('pu.updateDate < :threshold')
            ->andWhere('pu.status NOT IN (:finalStatuses)')
            ->setParameter('threshold', $thresholdDate)
            ->setParameter('finalStatuses', [
                ImplementationStatus::COMPLETED->value,
                ImplementationStatus::ABANDONED->value
            ])
            ->orderBy('pu.updateDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche dans les mises à jour
     */
    public function search(string $searchTerm): array
    {
        return $this->createQueryBuilder('pu')
            ->leftJoin('pu.commitment', 'c')
            ->leftJoin('c.proposition', 'p')
            ->leftJoin('pu.electedList', 'el')
            ->leftJoin('el.city', 'city')
            ->addSelect('c', 'p', 'el', 'city')
            ->andWhere('pu.description LIKE :search OR p.title LIKE :search OR city.name LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->orderBy('pu.updateDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
