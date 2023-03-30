<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController {
    private $em;

    public function __construct(EntityManagerInterface $entitiyManager) {
        $this->em = $entitiyManager;
    }

    /**
     * @Route("/buecher", name="book")
     */
    public function index()
    {
        return $this->render('book/index.html.twig', [
            "pageTextElements" => array(
                "title" => "Vegane Rezeptbücher der bei uns gelisteten Blogs",
                "breadcrumb" => array(
                    array("url" => $this->generateUrl("book"), "label" => "Rezeptbücher")
                )
            ),
            "books" => $this->em->getRepository(Book::class)->findAll()
        ]);
    }
}
