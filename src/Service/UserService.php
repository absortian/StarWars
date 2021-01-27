<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Event\UserRegisterEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserService extends AbstractController
{

    public function __construct(EventDispatcherInterface $eventDispatcher){
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * enableUser habilita un usuario
     */
    public function enableUser($user){
        
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado habilitando usuario."
        );
        
        if(isset($user) && $user instanceof User){

            $em = $this->getDoctrine()->getManager();
            $user->setEnabled(true);
            $em->persist($user);
            $em->flush($user);

            $response = array(
                "status" => true,
                "message" => "Realizado correctamente"
            );

        }else{

            $response = array(
                "status" => false,
                "message" => "Fallo en param."
            );

        }

        return $response;

    }

    /**
     * disableUser deshabilita un usuario
     */
    public function disableUser($user){
        
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado deshabilitando usuario."
        );
        
        if(isset($user) && $user instanceof User){

            $em = $this->getDoctrine()->getManager();
            $user->setEnabled(false);
            $em->persist($user);
            $em->flush($user);

            $response = array(
                "status" => true,
                "message" => "Realizado correctamente"
            );

        }else{

            $response = array(
                "status" => false,
                "message" => "Fallo en param."
            );

        }

        return $response;

    }

    /**
     * registerUser Registra a un usuario.
     */
    public function registerUser($email, $password){
        
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado creando usuario."
        );
        
        if(isset($email) && is_string($email) && !empty($email)){

            if(isset($password) && is_string($password) && !empty($password)){

                $em = $this->getDoctrine()->getManager();

                $user = new User();
                $user->setEmail($email);
                // ROL POR DEFECTO
                $roles = array("ROLE_USER");
                $user->setRoles($roles);
                $user->setEnabled(true);
                $user->setPassword(password_hash($password,1));
                $em->persist($user);
                $em->flush($user);

                // Lanzamos evento de usuario registrado
                $event = new UserRegisterEvent($user);
                $this->eventDispatcher->dispatch(UserRegisterEvent::NAME, $event);

                $response = array(
                    "status" => true,
                    "message" => "Usuario creado correctamente.",
                    "data" => array(
                        "user" => $user,
                    )
                );

            }else{

                $response = array(
                    "status" => false,
                    "message" => "Contraseña no válida."
                );

            }

        }else{

            $response = array(
                "status" => false,
                "message" => "Email no válido."
            );

        }

        return $response;

    }
    

}