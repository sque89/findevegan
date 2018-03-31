<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Blog;
use App\Service\CrawlService;

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
     * @Route("/crawl/{id}", name="crawlSingle")
     */
    public function single($id, CrawlService $crawlService, EntityManagerInterface $entityManager) {
        $blogRepository = $this->getDoctrine()->getRepository(Blog::class);
        $blog = $blogRepository->find($id);
        foreach($crawlService->getLatestBlogRecipes($blog) as $recipe) {
            $entityManager->persist($recipe);
        }
        $entityManager->flush();

        return $this->render('crawl/index.html.twig', [
                    'controller_name' => 'CrawlController',
        ]);
    }

}
