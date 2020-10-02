<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImprintController extends AbstractController
{
    /**
     * @Route("/impressum", name="imprint")
     */
    public function index()
    {
        return $this->render('imprint/index.html.twig', [
            'pageTextElements' => array(
                'title' => 'Impressum',
                'breadcrumb' => array(
                    array('label' => 'Impressum', 'url' => $this->generateUrl('imprint'))
                 )
            ),
        ]);
    }
}
