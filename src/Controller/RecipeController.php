<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipe;
use App\Entity\Blog;
use App\Entity\Searchterm;
use Symfony\Component\HttpFoundation\Request;

class RecipeController extends Controller {

    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
    }

    private function handleSearchTerm($term) {
        $alreadyExistingTerm = $this->em->getRepository(Searchterm::class)->findByTerm(mb_strtolower($term));
        if ($alreadyExistingTerm) {
            $alreadyExistingTerm->setCount($alreadyExistingTerm->getCount() + 1);
            $alreadyExistingTerm->setLatestSearch(new \DateTimeImmutable());
        } else {
            $newTerm = new Searchterm();
            $newTerm->setTerm(mb_strtolower($term));
            $newTerm->setFirstSearch(new \DateTimeImmutable());
            $newTerm->setLatestSearch(new \DateTimeImmutable());
            $newTerm->setCount(1);
            $this->em->persist($newTerm);
        }
        $this->em->flush();
    }

    /**
     * @Route("/", name="latest")
     * @Route("/suche/{term}", name="latestWithTerm")
     */
    public function latest(Request $request, string $term = null) {
        $recipePaginator = null;
        $uglyTerm = $request->query->get("q");

        if ($uglyTerm !== null && preg_match("/^[0-9a-zöüäßÖÜÄ\+]+$/i", $uglyTerm)) {
            return $this->redirectToRoute('latestWithTerm', array("term" => $uglyTerm), 301);
        }

        if ($term) {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByTerm($term, $request->query->get("page", 1));
            $this->handleSearchTerm($term);
        } else {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findLatest($request->query->get("page", 1));
        }

        return $this->render('recipe/list.html.twig', [
                    'controller_name' => 'RecipeController',
                    'recipes' => $recipePaginator,
                    'term' => $term
        ]);
    }

    /**
     * @Route("/blog/{slug}", name="blog")
     * @Route("/blog/{slug}/suche/{term}", name="blogWithTerm")
     */
    public function blog(Request $request, string $slug, string $term = null) {
        $recipePaginator = null;
        $uglyTerm = $request->query->get("q");

        if ($uglyTerm !== null && preg_match("/^[0-9a-zöüäßÖÜÄ\+]+$/i", $uglyTerm)) {
            return $this->redirectToRoute('blogWithTerm', array("slug" => $slug, "term" => $uglyTerm), 301);
        }

        if ($term) {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByBlogSlugAndTerm($slug, $term, $request->query->get("page", 1));
            $this->handleSearchTerm($term);
        } else {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByBlogSlug($slug, $request->query->get("page", 1));
        }

        return $this->render('recipe/list.html.twig', [
            'controller_name' => 'RecipeController',
            'recipes' => $recipePaginator,
            'term' => $term
        ]);
    }

    /**
     * @Route("/kategorie/{slug}", name="category")
     * @Route("/kategorie/{slug}/suche/{term}", name="categoryWithTerm")
     */
    public function category(Request $request, $slug, $term = null) {
        $recipePaginator = null;
        $uglyTerm = $request->query->get("q");

        if ($uglyTerm !== null && preg_match("/^[0-9a-zöüäßÖÜÄ\+]+$/i", $uglyTerm)) {
            return $this->redirectToRoute('categoryWithTerm', array("slug" => $slug, "term" => $uglyTerm), 301);
        }

        if ($term) {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByCategorySlugAndTerm($slug, $term, $request->query->get("page", 1));
            $this->handleSearchTerm($term);
        } else {
            $recipePaginator = $this->em->getRepository(Recipe::class)->findByCategorySlug($slug, $request->query->get("page", 1));
        }

        return $this->render('recipe/list.html.twig', [
            'controller_name' => 'RecipeController',
            'recipes' => $recipePaginator,
            'term' => $term
        ]);
    }

    public function statistic() {
        $numberOfRecipes = $this->em->getRepository(Recipe::class)->findNumberOfRecipes();
        $numberOfBlogs = $this->em->getRepository(Blog::class)->findNumberOfBlogs();
        return $this->render(
                        'recipe/statistic.html.twig', array('numberOfRecipes' => $numberOfRecipes, 'numberOfBlogs' => $numberOfBlogs)
        );
    }

}
