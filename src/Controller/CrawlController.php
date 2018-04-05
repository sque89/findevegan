<?php

namespace App\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Blog;
use App\Entity\Recipe;
use App\Service\CrawlService;
use Symfony\Component\HttpFoundation\Request;

class CrawlController extends AbstractController {

    private $crawlService;
    private $entityManager;

    public function __construct(CrawlService $crawlService, EntityManagerInterface $entityManager) {
        $this->crawlService = $crawlService;
        $this->entityManager = $entityManager;
    }

    private function crawlRecipeList(Crawler $recipeList, Blog $blog, bool $continueOnDuplicate) {
        $recipeRepository = $this->entityManager->getRepository(Recipe::class);
        foreach ($recipeList as $recipeNode) {
            $newRecipe = $this->crawlService->fetchRecipe(new Crawler($recipeNode), $blog);
            $alreadyExistingRecipe = $recipeRepository->findOneByPermalink($newRecipe->getPermalink());
            if ($alreadyExistingRecipe) {
                // Wenn alle Rezpete erneut gecrawlt werden sollen
                if ($continueOnDuplicate === 'true') {
                    if ($alreadyExistingRecipe->getImage() && $newRecipe->getImage()) {
                        unlink('images/' . $newRecipe->getImage() . ".jpg");
                    } else {
                        $alreadyExistingRecipe->setImage($newRecipe->getImage());
                    }
                } else {
                    if ($newRecipe->getImage()) {
                        unlink('images/' . $newRecipe->getImage() . ".jpg");
                    }
                    echo "vorhandenes rezept erreicht. blog wieder aktuell";
                    throw new Exception("Vorhandenes Rezept erreicht");
                }
            } else {
                $this->entityManager->persist($newRecipe);
            }
            $this->entityManager->flush();
        }
    }

    /**
     * continueOnDuplicate - wenn gesetzt wird nach bereits vorhandenem Rezept nicht gestoppt
     * @Route("/crawl/{id}", name="crawlSingle")
     */
    public function single(Request $request, $id) {
        set_time_limit(0);
        $blog = $this->entityManager->getRepository(Blog::class)->find($id);

        if ($blog->getType() === "wordpress") {
            $currentPage = 1;

            try {
                $pageCrawler = new Crawler(file_get_contents($blog->getFeed()));
                while ($pageCrawler->filter('item')->count() > 0) {
                    // Nicht abbrechbare each schleife keine option, da dann blogspot immer komplett gecrawlt wird
                    $this->crawlRecipeList($pageCrawler->filter('item'), $blog, $request->query->get("continueOnDuplicate", false));
                    $pageCrawler = new Crawler(file_get_contents($blog->getFeed() . '?paged=' . ++$currentPage));
                }
            } catch (\Exception $exception) {
                echo "Blog nicht gefunden oder Ende erreicht";
            }
        } else if ($blog->getType() === "blogspot") {
            try {
                $pageCrawler = new Crawler(file_get_contents($blog->getFeed() . "?max-results=99999"));
                $recipeItems = null;

                if ($pageCrawler->filterXPath('//item')->count() > 0) {
                    $recipeItems = $pageCrawler->filterXPath('//item');
                } else if ($pageCrawler->filterXPath('//entry')->count() > 0) {
                    $recipeItems = $pageCrawler->filterXPath('//entry');
                } else if ($pageCrawler->filterXPath('//default:item')->count() > 0) {
                    $recipeItems = $pageCrawler->filterXPath('//default:item');
                } else if ($pageCrawler->filterXPath('//default:entry')->count() > 0) {
                    $recipeItems = $pageCrawler->filterXPath('//default:entry');
                }

                $this->crawlRecipeList($recipeItems, $blog, $request->query->get("continueOnDuplicate", false));
            } catch (Exception $exception) {
                echo "Blog nicht gefunden oder Ende erreicht";
            }
        } else if ($this->blogtype == "wix") {
            $pageCrawler = new Crawler(file_get_contents($blog->getFeed()));
            $pageCrawler->each(function(Crawler $recipeNode) use ($blog, $crawlService, $entityManager) {
                $entityManager->persist($crawlService->fetchRecipe($recipeNode, $blog));
                $entityManager->flush();
            });
        } else if ($this->blogtype == "weebly") {
            $pageCrawler = new Crawler(file_get_contents($blog->getFeed()));
            $pageCrawler->each(function(Crawler $recipeNode) use ($blog, $crawlService, $entityManager) {
                $entityManager->persist($crawlService->fetchRecipe($recipeNode, $blog));
                $entityManager->flush();
            });
        } else {
            $curl = curl_init();
            curl_setopt_array($curl, Array(
                CURLOPT_URL => $blog->getFeed(),
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => 'UTF-8',
                CURLOPT_FOLLOWLOCATION => 1
            ));

            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');

            $pageCrawler = new Crawler(curl_exec($curl));
            $pageCrawler->each(function(Crawler $recipeNode) use ($blog, $crawlService, $entityManager) {
                $entityManager->persist($crawlService->fetchRecipe($recipeNode, $blog));
                $entityManager->flush();
            });
        }

        return $this->render('crawl/index.html.twig', [
                    'controller_name' => 'CrawlController',
        ]);
    }

}
