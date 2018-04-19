<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Entity\Blog;
use App\Entity\Searchterm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecipeController extends Controller {

    private $em;
    private $router;

    public function __construct(EntityManagerInterface $entitiyManager, UrlGeneratorInterface $router) {
        $this->em = $entitiyManager;
        $this->router = $router;
    }

    private function handleUglyTerm($term, $routeToRedirect, $routeParameters = []) {
        if ($term && preg_match("/^[0-9a-zöüäßÖÜÄ\+]+$/i", $term)) {
            $routeParameters["term"] = $term;
            return $this->redirectToRoute($routeToRedirect, $routeParameters, 301);
        }
        return null;
    }

    private function handleSearchTerm($term) {
        if ($term) {
            $alreadyExistingTerm = $this->em->getRepository(Searchterm::class)->findByTerm(mb_strtolower($term));
            if ($alreadyExistingTerm) {
                $alreadyExistingTerm->setCount($alreadyExistingTerm->getCount() + 1);
                $alreadyExistingTerm->setLatestSearch(new \DateTimeImmutable());
                $alreadyExistingTerm->setLatestResultCount($this->em->getRepository(Recipe::class)->findNumberOfRecpiesByTerm($term));
            } else {
                $newTerm = new Searchterm();
                $newTerm->setTerm(mb_strtolower($term));
                $newTerm->setFirstSearch(new \DateTimeImmutable());
                $newTerm->setLatestSearch(new \DateTimeImmutable());
                $newTerm->setLatestResultCount($this->em->getRepository(Recipe::class)->findNumberOfRecpiesByTerm($term));
                $newTerm->setCount(1);
                $this->em->persist($newTerm);
            }
            $this->em->flush();
        }
    }

    private function getCategoryList($routeName, $parameters = []) {
        $categories = [];
        foreach ($this->em->getRepository(RecipeCategory::class)->findAll() as $category) {
            $categories[] = array(
                'title' => $category->getTitle(),
                'path' => $this->router->generate($routeName, array_merge(array('categorySlug' => $category->getSlug()), $parameters))
            );
        }
        return $categories;
    }

    private function getPageInfo($page = 1, $category = null, $blog = null, $term = null) {
        $title = "Die aktuellsten veganen Rezpete";
        $headline = "Aktuell";

        if ($category && $blog && $term) {
            $title = sprintf("vegane Rezepte mit \"%s\" bei %s // %s", $term, $blog->getTitle(), $category->getTitle());
            $headline = sprintf("Ergebnisse mit \"%s\" bei %s // %s", $term, $blog->getTitle(), $category->getTitle());
        } else if ($category && $blog) {
            $title = sprintf("vegane Rezepte in %s // %s", $blog->getTitle(), $category->getTitle());
            $headline = sprintf("Rezepte in %s // %s", $blog->getTitle(), $category->getTitle());
        } else if ($blog && $term) {
            $title = sprintf("vegane Rezepte mit \"%s\" bei %s", $term, $blog->getTitle() ?? $category->getTitle());
            $headline = sprintf("Rezepte mit \"%s\" bei %s", $term, $blog->getTitle() ?? $category->getTitle());
        } else if ($category && $term) {
            $title = sprintf("vegane Rezepte mit \"%s\" in %s", $term, $category->getTitle() ?? $category->getTitle());
            $headline = sprintf("Rezepte mit \"%s\" in %s", $term, $category->getTitle() ?? $category->getTitle());
        } else if ($blog) {
            $title = sprintf("vegane Rezepte von %s", $blog->getTitle());
            $headline = sprintf("Rezepte von %s", $blog->getTitle());
        } else if ($category) {
            $title = sprintf("vegane Rezepte in %s", $category->getTitle());
            $headline = sprintf("Rezepte in %s", $category->getTitle());
        } else if ($term) {
            $title = sprintf("vegane Rezepte mit \"%s\"", $term);
            $headline = sprintf("Ergebnisse mit \"%s\"", $term);
        }

        return array("title" => $title, "headline" => $headline);
    }

    /**
     * @Route("/", name="index")
     */
    public function index() {
        return $this->redirectToRoute('latest', [], 301);
    }

    /**
     * @Route("/rezepte/", name="latest")
     * @Route("/rezepte/suche/{term}", name="latestWithTerm")
     */
    public function latest(Request $request, string $term = null) {
        $this->handleSearchTerm($term);

        return $this->handleUglyTerm($request->query->get("q"), 'latestWithTerm') ??
                $this->render('recipe/list.html.twig', [
                    'recipes' => $this->em->getRepository(Recipe::class)->findRecipeListForCriterias($request->query->get("page", 1), null, null, $term),
                    'term' => $term,
                    'searchPlaceholder' => 'Suche in allen Rezepten',
                    'categories' => $this->getCategoryList('category'),
                    'pageInfo' => $this->getPageInfo($request->query->get("page", 1), null, null, $term)
        ]);
    }

    /**
     * @Route("/rezepte/blog/{blogSlug}", name="blog")
     * @Route("/rezepte/blog/{blogSlug}/kategorie/{categorySlug}", name="blogWithCategory")
     * @Route("/rezepte/blog/{blogSlug}/suche/{term}", name="blogWithTerm")
     * @Route("/rezepte/blog/{blogSlug}/kategorie/{categorySlug}/suche/{term}", name="blogWithCategoryAndTerm")
     */
    public function blog(Request $request, string $blogSlug, string $categorySlug = null, string $term = null) {
        $blog = $this->em->getRepository(Blog::class)->findOneBySlug($blogSlug);
        $category = $this->em->getRepository(RecipeCategory::class)->findOneBySlug($categorySlug);
        $this->handleSearchTerm($term);
        $response = null;

        if ($categorySlug) {
            $response = $this->handleUglyTerm($request->query->get("q"), "blogWithCategoryAndTerm", array("blogSlug" => $blogSlug, "categorySlug" => $categorySlug));
        } else {
            $response = $this->handleUglyTerm($request->query->get("q"), "blogWithTerm", array("blogSlug" => $blogSlug));
        }

        return $response ??
            $this->render('recipe/list.html.twig', [
                'recipes' => $this->em->getRepository(Recipe::class)->findRecipeListForCriterias($request->query->get("page", 1), $categorySlug, $blogSlug, $term),
                'term' => $term,
                'searchPlaceholder' => "blub",
                'categories' => $this->getCategoryList('blogWithCategory', array('blogSlug' => $blog->getSlug())),
                'pageInfo' => $this->getPageInfo($request->query->get("page", 1), $category, $blog, $term)
            ]);
    }

    /**
     * @Route("/rezepte/kategorie/{categorySlug}", name="category")
     * @Route("/rezepte/kategorie/{categorySlug}/suche/{term}", name="categoryWithTerm")
     */
    public function category(Request $request, $categorySlug, $term = null) {
        $category = $this->em->getRepository(RecipeCategory::class)->findOneBySlug($categorySlug);
        $this->handleSearchTerm($term);

        return $this->handleUglyTerm($request->query->get("q"), 'categoryWithTerm', array("categorySlug" => $categorySlug)) ??
            $this->render('recipe/list.html.twig', [
                'recipes' => $this->em->getRepository(Recipe::class)->findRecipeListForCriterias($request->query->get("page", 1), $categorySlug, null, $term),
                'term' => $term,
                'searchPlaceholder' => "blub",
                'categories' => $this->getCategoryList('category'),
                'pageInfo' => $this->getPageInfo($request->query->get("page", 1), $category, null, $term)
        ]);
    }

    /**
     * Rendert das Statistik Widget
     * @return type
     */
    public function statistic() {
        $numberOfRecipes = $this->em->getRepository(Recipe::class)->findNumberOfRecipes();
        $numberOfBlogs = $this->em->getRepository(Blog::class)->findNumberOfBlogs();
        return $this->render(
                        'recipe/statistic.html.twig', array('numberOfRecipes' => $numberOfRecipes, 'numberOfBlogs' => $numberOfBlogs)
        );
    }

    /**
     * Rendert das Termcloud Widget
     * @return type
     */
    public function termCloud() {
        $mostUsedTerms = $this->em->getRepository(Searchterm::class)->findMostUsedTerms();
        $mostUsedTermsForTemplate = [];
        foreach ($mostUsedTerms as $key => $value) {
            $mostUsedTermsForTemplate[] = array("term" => $value, "level" => intdiv($key, 5));
        }
        shuffle($mostUsedTermsForTemplate);
        return $this->render(
                        'recipe/termcloud.html.twig', array('mostUsedTerms' => $mostUsedTermsForTemplate)
        );
    }

}
