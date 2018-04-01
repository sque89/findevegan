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

    /**
     * @Route("/crawl", name="crawlAll")
     */
    public function all() {
        return $this->render('crawl/index.html.twig', [
                    'controller_name' => 'CrawlController',
        ]);
    }

    /**
     * continueOnDuplicate - wenn gesetzt wird nach bereits vorhandenem Rezept nicht gestoppt
     * @Route("/crawl/{id}", name="crawlSingle")
     */
    public function single(Request $request, $id, CrawlService $crawlService, EntityManagerInterface $entityManager) {
        set_time_limit(0);
        $blogRepository = $entityManager->getRepository(Blog::class);
        $recipeRepository = $entityManager->getRepository(Recipe::class);
        $blog = $blogRepository->find($id);

        if ($blog->getType() === "wordpress") {
            $stop = false;
            $currentPage = 1;

            try {
                $pageCrawler = new Crawler(file_get_contents($blog->getFeed()));
                while ($pageCrawler->filter('item')->count() > 0 && !$stop) {
                    $pageCrawler->filter('item')->each(function(Crawler $recipeNode) use ($request, $blog, $crawlService, $entityManager, $recipeRepository, &$stop) {
                        $newRecipe = $crawlService->fetchRecipe($recipeNode, $blog);
                        $alreadyExistingRecipe = $recipeRepository->findOneByPermalink($newRecipe->getPermalink());
                        if (!$alreadyExistingRecipe) {
                            $entityManager->persist($newRecipe);
                        } else {
                            if ($request->query->get("continueOnDuplicate") !== 'true') {
                                unlink('images/' . $newRecipe->getImage() . ".jpg");
                                echo "vorhandenes rezept erreicht. blog wieder aktuell";
                                $stop = true;
                            } else {
                                if ($alreadyExistingRecipe->getImage()) {
                                    unlink('images/' . $newRecipe->getImage() . ".jpg");
                                } else {
                                    $alreadyExistingRecipe->setImage($newRecipe->getImage());
                                }
                            }
                        }
                        $entityManager->flush();
                    });
                    $pageCrawler = new Crawler(file_get_contents($blog->getFeed() . '?paged=' . ++$currentPage));
                }
            } catch (\Exception $exception) {
                echo "Blog nicht gefunden oder Ende erreicht";
            }
        } else if ($blog->getType() === "blogspot") {
            try {
                $pageCrawler = new Crawler(file_get_contents($blog->getFeed() . "?max-results=99999"));
                $recipeItems = null;

                if ($pageCrawler->filterXPath('//default:item')->count() > 0) {
                    echo "item";
                    $recipeItems = $pageCrawler->filterXPath('//default:item');
                } else if ($pageCrawler->filterXPath('//default:entry')->count() > 0) {
                    echo "entry";
                    $recipeItems = $pageCrawler->filterXPath('//default:entry');
                }

                $recipeItems->each(function(Crawler $recipeNode) use ($blog, $crawlService, $entityManager) {
                    $entityManager->persist($crawlService->fetchRecipe($recipeNode, $blog));
                    $entityManager->flush();
                });
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
