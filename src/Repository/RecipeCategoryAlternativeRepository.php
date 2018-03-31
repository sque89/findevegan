<?php

namespace App\Repository;

use App\Entity\RecipeCategoryAlternative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RecipeCategoryAlternative|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeCategoryAlternative|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeCategoryAlternative[]    findAll()
 * @method RecipeCategoryAlternative[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeCategoryAlternativeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RecipeCategoryAlternative::class);
    }

//    /**
//     * @return RecipeCategoryAlternative[] Returns an array of RecipeCategoryAlternative objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RecipeCategoryAlternative
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
