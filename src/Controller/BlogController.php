<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Blog;

class BlogController extends Controller
{
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
            'blogs' => $blogs
        ]);
    }

    public function ranking() {
        $blogs = $this->em->getRepository(Blog::class)->findMostActiveBlogs();
        return $this->render('blog/ranking.html.twig', [
            'blogs' => $blogs
        ]);
    }
}
