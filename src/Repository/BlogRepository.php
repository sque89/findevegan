<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Blog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blog[]    findAll()
 * @method Blog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    private function getBasicQueryBuilder() {
        return $this->createQueryBuilder('b')
                ->where('b.enabled = 1');
    }

    public function findBlogsByFirstLetter(string $letter) {
        $qb = $this->getBasicQueryBuilder();
        return $qb->add('where', $qb->expr()->eq($qb->expr()->lower($qb->expr()->substring('b.title', 1, 1)), ':letter'))
            ->setParameter('letter', $letter)
            ->getQuery()
            ->getResult();
    }

    public function findNumberOfBlogs() {
        return $this->getBasicQueryBuilder()
            ->select('count(b.id)')
            ->where('b.enabled = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMostActiveBlogs() {
        return $this->getBasicQueryBuilder()
            ->join('b.recipes', 'r')
            ->select('count(r.id) as count, b.title, b.slug')
            ->where('DATE_DIFF(CURRENT_TIMESTAMP(), r.released) < 30')
            ->groupBy('b.id')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findUsedCategoriesByBlogId($blogId) {
        return $this->getBasicQueryBuilder()
            ->select('c.title, c.slug')
            ->join('b.recipes', 'r')
            ->join('r.categories', 'c')
            ->distinct('c.id')
            ->where('b.id = :blogId')
            ->setParameter('blogId', $blogId)
            ->getQuery()
            ->getResult();
    }
}
