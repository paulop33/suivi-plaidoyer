<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Trouve toutes les catégories avec leurs propositions et engagements
     * Optimisé pour éviter les requêtes N+1
     */
    public function findAllWithPropositionsAndCommitments(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.propositions', 'p')
            ->leftJoin('p.commitments', 'cm')
            ->leftJoin('cm.candidateList', 'cl')
            ->leftJoin('cl.city', 'city')
            ->addSelect('p', 'cm', 'cl', 'city')
            ->orderBy('c.position', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une catégorie par id et slug avec toutes ses données liées
     */
    public function findOneByIdAndSlug(int $id, string $slug): ?Category
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.propositions', 'p')
            ->leftJoin('p.commitments', 'cm')
            ->leftJoin('cm.candidateList', 'cl')
            ->leftJoin('cl.city', 'city')
            ->addSelect('p', 'cm', 'cl', 'city')
            ->where('c.id = :id')
            ->andWhere('c.slug = :slug')
            ->setParameter('id', $id)
            ->setParameter('slug', $slug)
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Category[] Returns an array of Category objects
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

    //    public function findOneBySomeField($value): ?Category
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
