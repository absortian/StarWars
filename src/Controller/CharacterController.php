<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CharacterController extends AbstractController
{
    /**
     * @Route("/delete/characters", name="delete_characters")
     */
    public function deleteCharacters(Request $request){
        $response = array(
            "status" => false,
            "message" => "Fail"
        );
        $entityManager = $this->getDoctrine()->getManager();
        $character = $entityManager->getRepository("App:Characters")->findOneById($request->get("id"));
        if($character){
            $entityManager->remove($character);
            $entityManager->flush();
            $response = array(
                "status" => true
            );
        }else{
            $response = array(
                "status" => true
            );
        }
        return new JsonResponse($response);
    }
    
    /**
     * @Route("/save/character", name="save_character")
     */
    public function saveCharacter(Request $request){
        $response = array(
            "status" => false,
            "message" => "Fail"
        );
        $entityManager = $this->getDoctrine()->getManager();
        $character = $entityManager->getRepository("App:Characters")->findOneById($request->get("id"));
        if($character){
            if($request->get("name") != $character->getName()){
                $character->setName($request->get("name"));
            }
            if($request->get("gender") != $character->getGender()){
                $character->setGender($request->get("gender"));
            }
            if($request->get("mass") != $character->getMass()){
                $character->setMass(floatval($request->get("mass")));
            }
            if($request->get("height") != $character->getHeight()){
                $character->setHeight(floatval($request->get("height")));
            }
            foreach($request->files as $file){
                $partes = explode(".", $file->getClientOriginalName());
                $ext = $partes[(count($partes)-1)];
                $filename = uniqid().".".$ext;
                $file->move("characters/", $filename);
                $character->setPicture("characters/".$filename);
            }
            $entityManager->flush($character);
            $response = array(
                "status" => true
            );
        }else{
            $response = array(
                "status" => false
            );
        }
        return new JsonResponse($response);
    }

}