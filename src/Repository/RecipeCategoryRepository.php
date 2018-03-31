<?php

namespace App\Repository;

use App\Entity\RecipeCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RecipeCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeCategory[]    findAll()
 * @method RecipeCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeCategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RecipeCategory::class);
    }

//    /**
//     * @return RecipeCategory[] Returns an array of RecipeCategory objects
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


    public function findOneBySlugOrAlternative($value): ?RecipeCategory {
        return $this->createQueryBuilder('r')
            ->addSelect('a')
            ->join('r.alternatives', 'a')
            ->andWhere('LOWER(r.slug) = :slug')
            ->orWhere('LOWER(a.slug) = :slug')
            ->setParameter('slug', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
