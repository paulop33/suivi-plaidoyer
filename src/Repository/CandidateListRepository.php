<?php

namespace App\Repository;

use App\Entity\CandidateList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CandidateList>
 */
class CandidateListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateList::class);
    }

    /**
     * Trouve toutes les listes candidates avec leurs engagements
     * Optimisé pour éviter les requêtes N+1
     */
    public function findAllWithCommitments(): array
    {
        return $this->createQueryBuilder('cl')
            ->leftJoin('cl.commitments', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->leftJoin('cl.city', 'c')
            ->addSelect('cm', 'p', 'cat', 'c')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une liste candidate avec tous ses engagements
     */
    public function findOneWithCommitments(int $id): ?CandidateList
    {
        return $this->createQueryBuilder('cl')
            ->leftJoin('cl.commitments', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->leftJoin('cl.city', 'c')
            ->addSelect('cm', 'p', 'cat', 'c')
            ->where('cl.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les listes les plus actives (avec le plus d'engagements)
     */
    public function findMostActiveLists(int $limit = 10): array
    {
        return $this->createQueryBuilder('cl')
            ->select('cl', 'COUNT(cm.id) as totalCommitments')
            ->leftJoin('cl.commitments', 'cm')
            ->leftJoin('cl.city', 'c')
            ->addSelect('c')
            ->groupBy('cl.id')
            ->orderBy('totalCommitments', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les listes d'une commune avec leurs statistiques
     */
    public function findByCityWithStats(int $cityId): array
    {
        return $this->createQueryBuilder('cl')
            ->select('cl', 'COUNT(cm.id) as totalCommitments', 'COUNT(DISTINCT cat.id) as categoriesCount')
            ->leftJoin('cl.commitments', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->where('cl.city = :cityId')
            ->setParameter('cityId', $cityId)
            ->groupBy('cl.id')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return CandidateList[] Returns an array of CandidateList objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CandidateList
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
