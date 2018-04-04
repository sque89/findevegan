<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Recipe::class);
    }

    public function findLatest() {
        return $this->createQueryBuilder('r')
                        ->where('r.enabled = true')
                        ->setMaxResults(20)
                        ->getQuery()
                        ->getResult();
    }

    public function findByBlogSlug($slug) {
        return $this->createQueryBuilder('r')
                        ->join('r.blog', 'b')
                        ->where('b.slug = :slug')
                        ->setMaxResults(20)
                        ->setParameter(':slug', $slug)
                        ->getQuery()
                        ->getResult();
    }

    public function findOneByPermalink($permalink): ?Recipe {
        return $this->createQueryBuilder('r')
                        ->andWhere('r.permalink = :val')
                        ->setParameter('val', $permalink)
                        ->getQuery()
                        ->getOneOrNullResult();
    }
}
