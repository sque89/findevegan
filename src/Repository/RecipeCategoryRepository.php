<?php

namespace App\Repository;

use App\Entity\RecipeCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeCategory[]    findAll()
 * @method RecipeCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeCategory::class);
    }

    public function findOneBySlugOrAlternative($value): ?RecipeCategory {
        return $this->createQueryBuilder('r')
            ->addSelect('a')
            ->join('r.alternatives', 'a')
            ->where('LOWER(r.slug) = :slug')
            ->orWhere('LOWER(a.slug) = :slug')
            ->setParameter('slug', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
