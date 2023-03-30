<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Blog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController {
    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
    }

    /**
     * @Route("/blogs/{firstLetter}", name="blogsStartWithLetter")
     */
    public function blogs($firstLetter) {
        $blogs = [];
        foreach($this->em->getRepository(Blog::class)->findBlogsByFirstLetter($firstLetter) as $blog) {
            $blogs[] = [
                "blog" => $blog,
                "usedCategories" => $this->em->getRepository(Blog::class)->findUsedCategoriesByBlogId($blog->getId())
            ];
        }
        return $this->render('blog/list.html.twig', [
            'blogs' => $blogs,
            'pageTextElements' => array(
                "title" => "Vegane Foodblogs mit \"" . $firstLetter . "\"",
                "breadcrumb" => array(
                    array(
                        "url" => $this->generateUrl("blogsStartWithLetter", array("firstLetter" => "a")),
                        "label" => "Blogs"
                    ),
                    array(
                        "url" => $this->generateUrl("blogsStartWithLetter", array("firstLetter" => $firstLetter)),
                        "label" => "beginnend mit " . $firstLetter
                    )
                )
            )
        ]);
    }

    public function ranking() {
        $blogs = $this->em->getRepository(Blog::class)->findMostActiveBlogs();
        return $this->render('blog/ranking.html.twig', [
            'blogs' => $blogs
        ]);
    }

    public function letterSelection() {
        $allLetters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $lettersToRender = [];
        foreach ($allLetters as $letter) {
            if (count($this->em->getRepository(Blog::class)->findBlogsByFirstLetter($letter)) > 0) {
                $lettersToRender[] = $letter;
            }
        }
        return $this->render('blog/letterSelection.html.twig', [
            'letters' => $lettersToRender
        ]);
    }
}
