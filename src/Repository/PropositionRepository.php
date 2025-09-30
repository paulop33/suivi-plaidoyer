<?php

namespace App\Repository;

use App\Entity\Proposition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Proposition>
 */
class PropositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proposition::class);
    }

    /**
     * Trouve toutes les propositions avec leurs engagements
     * Optimisé pour éviter les requêtes N+1
     */
    public function findAllWithCommitments(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.commitments', 'cm')
            ->leftJoin('cm.candidateList', 'cl')
            ->leftJoin('cl.city', 'c')
            ->leftJoin('p.category', 'cat')
            ->addSelect('cm', 'cl', 'c', 'cat')
            ->orderBy('cat.position', 'ASC')
            ->addOrderBy('p.position', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une proposition avec tous ses engagements et attentes spécifiques
     */
    public function findOneWithCommitments(int $id): ?Proposition
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.commitments', 'cm')
            ->leftJoin('cm.candidateList', 'cl')
            ->leftJoin('cl.city', 'c')
            ->leftJoin('p.category', 'cat')
            ->leftJoin('p.specificExpectations', 'se')
            ->leftJoin('se.specificity', 's')
            ->addSelect('cm', 'cl', 'c', 'cat', 'se', 's')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les propositions les plus signées
     */
    public function findMostSignedPropositions(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'COUNT(cm.id) as totalSignatures')
            ->leftJoin('p.commitments', 'cm')
            ->leftJoin('p.category', 'cat')
            ->addSelect('cat')
            ->groupBy('p.id')
            ->orderBy('totalSignatures', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
//     * @return Proposition[] Returns an array of Proposition objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Proposition
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
