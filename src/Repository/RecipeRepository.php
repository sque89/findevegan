<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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

    private function getBasicQueryBuilder() {
        return $this->createQueryBuilder('r')
            ->where('r.enabled = 1')
            ->orderBy('r.released', 'DESC');
    }

    private function getPaginator($queryBuilder, $currentPage) {
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage(40);
        $paginator->setCurrentPage($currentPage);
        return $paginator;
    }

    public function findLatest($page) {
        return $this->getPaginator($this->createQueryBuilder('r')->where('r.enabled = true'), $page);
    }

    public function findByTerm($term, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->andWhere('r.title LIKE :term')
                ->setParameter(':term', '%'.$term.'%'),
            $page
        );
    }

    public function findByBlogSlug($slug, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->join('r.blog', 'b')
                ->andWhere('b.slug = :slug')
                ->andWhere('b.enabled = 1')
                ->setParameter(':slug', $slug),
            $page
        );
    }

    public function findByBlogSlugAndTerm($slug, $term, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->join('r.blog', 'b')
                ->andWhere('b.slug = :slug')
                ->andWhere('r.title LIKE :term')
                ->setParameter(':slug', $slug)
                ->setParameter(':term', '%'.$term.'%'),
            $page);
    }

    public function findByCategorySlug($slug, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->join('r.categories', 'c')
                ->andWhere('c.slug = :slug')
                ->setParameter(':slug', $slug),
            $page);
    }

    public function findByCategorySlugAndTerm($slug, $term, $page) {
        return $this->getPaginator(
            $this->getBasicQueryBuilder()
                ->join('r.categories', 'c')
                ->andWhere('c.slug = :slug')
                ->andWhere('r.title LIKE :term')
                ->setParameter(':slug', $slug)
                ->setParameter(':term', '%'.$term.'%'),
            $page);
    }

    public function findOneByPermalink($permalink): ?Recipe {
        return $this->getBasicQueryBuilder()
            ->andWhere('r.permalink = :val')
            ->setParameter('val', $permalink)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNumberOfRecipes() {
        return $this->getBasicQueryBuilder()
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
