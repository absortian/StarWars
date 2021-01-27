<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/characters", name="search_characters")
     */
    public function searchCharacters(Request $request){
        $response = array(
            "status" => false,
            "message" => "Fail"
        );
        $entityManager = $this->getDoctrine()->getManager();
        $characters = $entityManager->getRepository("App:Characters")->findCharacters($request->get("search"));
        if($characters){
            $response = array(
                "status" => true,
                "data" => $characters
            );
        }else{
            $response = array(
                "status" => true,
                "data" => array()
            );
        }
        return new JsonResponse($response);
    }
    
}