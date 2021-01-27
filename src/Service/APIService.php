<?php

namespace App\Service;

use App\Entity\Characters;
use App\Entity\Movies;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class APIService extends AbstractController
{

    public function __construct(){

    }

    /**
     * Función que importa personaje
     */
    public function importCharacter($URL,Movies $movie = null){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $entityManager = $this->getDoctrine()->getManager();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $URL);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if($result){
            if(is_array($result) && isset($result["name"])){
                // Check
                $characterCheck = $entityManager->getRepository("App:Characters")->findOneByName($result["name"]);
                if(!$characterCheck){
                    $newCharacter = new Characters();
                    $newCharacter->setName($result["name"]);
                    $newCharacter->setGender($result["gender"]);
                    $newCharacter->setMass(floatval($result["mass"]));
                    $newCharacter->setHeight(floatval($result["height"]));
                    $entityManager->persist($newCharacter);
                    $entityManager->flush($newCharacter);
                    if($movie){
                        $newCharacter->addMovie($movie);
                        $movie->addCharacter($newCharacter);
                        $entityManager->flush($newCharacter);
                        $entityManager->flush($movie);
                    }
                }else{
                    if($movie){
                        $characterCheck->addMovie($movie);
                        $movie->addCharacter($characterCheck);
                        $entityManager->flush($characterCheck);
                        $entityManager->flush($movie);
                    }
                }
                $response = array(
                    "status" => true,
                    "message" => "Done"
                );
            }
        }
        return $response;
    }

    /**
     * Función que importa todas las pelis y personajes
     */
    public function importAllMovies(){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $entityManager = $this->getDoctrine()->getManager();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,"https://swapi.dev/api/films/");
        $result = curl_exec($ch);
        curl_close($ch);
        $movies = json_decode($result, true);
        if(isset($movies["results"])){
            $movies = $movies["results"];
            foreach ($movies as $key => $movie) {
                if(is_array($movie) && isset($movie["title"])){
                    // Check
                    $movieCheck = $entityManager->getRepository("App:Movies")->findOneByName($movie["title"]);
                    if(!$movieCheck){
                        $newMovie = new Movies();
                        $newMovie->setName($movie["title"]);
                        $entityManager->persist($newMovie);
                        $entityManager->flush($newMovie);
                    }
                    foreach ($movie["characters"] as $characterURL) {
                        // Replace secure
                        $auxURL = str_replace("http://","https://", $characterURL);
                        if($movieCheck){
                            $this->importCharacter($auxURL, $movieCheck);
                        }else{
                            $this->importCharacter($auxURL, $newMovie);
                        }
                    }
                }
            }
            $response = array(
                "status" => true,
                "message" => "Hecho"
            );
        }
        return $response;
    }

}