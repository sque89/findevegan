<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Blog;

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
    public function single($id) {
        $blogRepository = $this->getDoctrine()->getRepository(Blog::class);
        $blogRepository->find($id)->crawl();

        return $this->render('crawl/index.html.twig', [
                    'controller_name' => 'CrawlController',
        ]);
    }

}
