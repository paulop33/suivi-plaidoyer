<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * Trouve toutes les communes avec leurs listes et engagements
     * Optimisé pour éviter les requêtes N+1
     */
    public function findAllWithListsAndCommitments(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.contacts', 'cl')
            ->leftJoin('cl.commitments', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->addSelect('cl', 'cm', 'p', 'cat')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une commune avec toutes ses données liées
     */
    public function findOneWithAllData(int $id): ?City
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.contacts', 'cl')
            ->leftJoin('cl.commitments', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->addSelect('cl', 'cm', 'p', 'cat')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve une commune par son slug avec toutes ses données liées
     */
    public function findOneBySlugWithAllData(string $slug): ?City
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.contacts', 'cl')
            ->leftJoin('cl.commitments', 'cm')
            ->leftJoin('cm.proposition', 'p')
            ->leftJoin('p.category', 'cat')
            ->addSelect('cl', 'cm', 'p', 'cat')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve une commune par son slug
     */
    public function findOneBySlug(string $slug): ?City
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtient les statistiques d'engagement par commune
     */
    public function getCityEngagementStats(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id', 'c.name', 'COUNT(DISTINCT cl.id) as totalLists', 'COUNT(cm.id) as totalCommitments')
            ->leftJoin('c.contacts', 'cl')
            ->leftJoin('cl.commitments', 'cm')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les communes les plus actives (avec le plus d'engagements)
     */
    public function findMostActiveCities(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'COUNT(cm.id) as totalCommitments')
            ->leftJoin('c.contacts', 'cl')
            ->leftJoin('cl.commitments', 'cm')
            ->groupBy('c.id')
            ->orderBy('totalCommitments', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return City[] Returns an array of City objects
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

    //    public function findOneBySomeField($value): ?City
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
