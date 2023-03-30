<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DataProtectionController extends AbstractController {
    /**
     * @Route("/datenschutz", name="data_protection")
     */
    public function index()
    {
        return $this->render('data_protection/index.html.twig', [
            'pageTextElements' => array(
                'title' => 'Datenschutz',
                'breadcrumb' => array(
                    array('url' => $this->generateUrl('data_protection'), 'label' => 'Datenschutz')
                )
            )
        ]);
    }
}
