<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Form\RegisterBlogType;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller {

    /**
     * @Route("/contact", name="contact")
     */
    public function index() {
        return $this->render('contact/index.html.twig', [
                    'controller_name' => 'ContactController',
        ]);
    }

    /**
     * @Route("/registrieren", name="registerBlog")
     */
    public function registerBlog(Request $request) {
        $form = $this->createForm(RegisterBlogType::class);
        $mailSent = false;
        $mailSuccess = false;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();

            $headerFields = array(
                "From: " . $contactFormData["mail"],
                "MIME-Version: 1.0",
                "Content-Type: text/html;charset=utf-8"
            );

            $message =
                "Blog-Registrierung: " . $contactFormData["name"] . "<br /><br />" .
                "Blog-URL" . $contactFormData["url"] . "<br />" .
                "Blog-URL" . $contactFormData["feed"] . "<br />" .
                "Blog-URL" . $contactFormData["message"] . "<br />";

            $mailSent = true;
            $mailSuccess = mail(
                "kontakt@findevegan.de",
                "Anfrage Blog-Registrierung: " . $contactFormData["name"],
                $message,
                implode("\r\n", $headerFields)
            );
        }

        return $this->render('contact/registerBlog.html.twig', [
                    'pageTextElements' => array(
                        'title' => 'Veganen Food-Blog registrieren',
                        'breadcrumb' => array(
                            array('label' => 'Blog registrieren', 'url' => $this->generateUrl('registerBlog'))
                        )
                    ),
                    'form' => $form->createView(),
                    'sent' => $mailSent,
                    'success' => $mailSuccess
        ]);
    }

}
