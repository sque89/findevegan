<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AboutController extends Controller {

    /**
     * @Route("/ueber", name="about")
     */
    public function index() {
        return $this->render('about/index.html.twig', [
            'pageTextElements' => array(
                "title" => "Vegane Foodblogs bei uns",
                'breadcrumb' => array(
                    array("url" => $this->generateUrl("about"), "label" => "Über uns")
                )
            )
        ]);
    }

}
