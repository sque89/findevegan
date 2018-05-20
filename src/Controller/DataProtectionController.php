<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DataProtectionController extends Controller
{
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
