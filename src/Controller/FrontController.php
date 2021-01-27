<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="homepage_front")
     */
    public function homepage(){

        //$entityManager = $this->getDoctrine()->getManager();

        return new Response(
            $this->renderView("front/front.html.twig")
        );
    }
    
}