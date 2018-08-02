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
            $existingRecipe = $recipeRepository->findOneByPermalink(CrawlService::parsePermalink(new Crawler($recipeNode)));
            if ($existingRecipe && $continueOnDuplicate === true) {
                $recipeDataForExistingRecipe = $this->crawlService->fetchRecipe(new Crawler($recipeNode), $blog);
                if ($recipeDataForExistingRecipe->getImage()) {
                    $existingRecipe->setImage($recipeDataForExistingRecipe->getImage());
                    $this->entityManager->flush();
                }
            } else if (!$existingRecipe) {
                $newRecipe = $this->crawlService->fetchRecipe(new Crawler($recipeNode), $blog);
                $this->entityManager->persist($newRecipe);
                $this->entityManager->flush();
            } else {
                echo "Vorhandenes Rezept erreicht<br />";
                return false;
            }
        }
        return true;
    }

    private function crawlMultiPageFeed($blog, $pageParameter, $updateAllImages) {
        $currentPage = 1;
        try {
            $pageCrawler = new Crawler(file_get_contents($blog->getFeed()));
            while ($pageCrawler->filter('item')->count() > 0) {
                if (!$this->crawlRecipeList($pageCrawler->filter('item'), $blog, $updateAllImages)) {
                    break;
                }
                $pageCrawler = new Crawler(file_get_contents($blog->getFeed() . '?' . $pageParameter . '=' . ++$currentPage));
            }
            $blog->setLatestSuccessfulCrawl(new \DateTimeImmutable());
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            echo "crawlSingle: " . $exception->getMessage() . "<br />";
        }
    }

    private function crawlSinglePageFeed($blog, $updateAllImages, $limitParameter = false) {
        try {
            $pageCrawler = new Crawler(file_get_contents($blog->getFeed() . ($limitParameter != false ? "?" . $limitParameter : '')));
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

            $this->crawlRecipeList($recipeItems, $blog, $updateAllImages);
            $blog->setLatestSuccessfulCrawl(new \DateTimeImmutable());
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            echo "crawlSingle: " . $exception->getMessage() . "<br />";
        }
    }

    private function crawlCurlFeed($blog, $updateAllImages) {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, Array(
                CURLOPT_URL => $blog->getFeed(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => 'UTF-8',
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'
            ));
            $pageCrawler = new Crawler(curl_exec($curl));

            if ($pageCrawler->filterXPath('//item')->count() > 0) {
                $recipeItems = $pageCrawler->filterXPath('//item');
            } else if ($pageCrawler->filterXPath('//entry')->count() > 0) {
                $recipeItems = $pageCrawler->filterXPath('//entry');
            } else if ($pageCrawler->filterXPath('//default:item')->count() > 0) {
                $recipeItems = $pageCrawler->filterXPath('//default:item');
            } else if ($pageCrawler->filterXPath('//default:entry')->count() > 0) {
                $recipeItems = $pageCrawler->filterXPath('//default:entry');
            }

            $this->crawlRecipeList($recipeItems, $blog, $updateAllImages);
            $blog->setLatestSuccessfulCrawl(new \DateTimeImmutable());
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            echo "crawlSingle: " . $exception->getMessage() . "<br />";
        }
    }

    /**
     * @param updateAllImages - wenn gesetzt wird nach bereits vorhandenem Rezept nicht gestoppt und die Bilder aller Rezepte aktualisiert
     * @Route("/crawl/{id}", name="crawlSingle")
     */
    public function single(Request $request, $id) {
        set_time_limit(0);
        $blog = $this->entityManager->getRepository(Blog::class)->find($id);
        $updateAllImages = $request->query->get("updateAllImages", false);

        if ($blog->getType() === "wordpress") {
            $this->crawlMultiPageFeed($blog, 'paged', $updateAllImages);
        } else if ($blog->getType() === "blogspot") {
            $this->crawlSinglePageFeed($blog, $updateAllImages, 'max-results=99999');
        } else if ($blog->getType() == "wix") {
            // TODO not implemented
        } else if ($blog->getType() == "weebly") {
            $this->crawlSinglePageFeed($blog, $updateAllImages);
        } else {
            $this->crawlCurlFeed($blog, $updateAllImages);
        }

        return $this->render('crawl/index.html.twig', [
                    'controller_name' => 'CrawlController',
        ]);
    }

}
