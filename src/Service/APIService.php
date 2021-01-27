<?php

namespace App\Service;

use App\Entity\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Entity\UserToken;
use DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class APIService extends AbstractController
{

    public function __construct(EventDispatcherInterface $eventDispatcher, UrlGeneratorInterface $router, UserService $userService){
        $this->userService = $userService;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    /**
     * Función que autentica un usuario a través del usuario de sesión o token
     */
    public function userAuth($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        if(isset($data["user"]) && $data["user"] instanceof User){
            // El usuario ha sido autenticado a partir de que ha hecho login en la plataforma
            $user = $data["user"];
        }else{
            // Debemos revisar si ha añadido un token válido
            if(isset($data["userToken"])){
                $checkToken = $this->checkToken($data);
                if(isset($checkToken["status"]) && $checkToken["status"]){
                    $user = $checkToken["user"];
                }else{
                    $response = $checkToken;
                }
            }else{
                $response = array(
                    "status" => false,
                    "message" => "El usuario o token no puede estar vacío."
                );
            }
        }
        if(isset($user)){
            $response = array(
                "status" => true,
                "user" => $user,
                "message" => "Auth OK"
            );
        }
        return $response;
    }

    /**
     * Función que crea un usuario en la plataforma
     */
    public function apiCreateUser($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $authUser = $this->userAuth($data);
        if(isset($authUser["status"]) && $authUser["status"]){
            $user = $authUser["user"];
            $data["user"] = $user;
            if(isset($data["email"]) && $data["email"]){
                if(isset($data["password"]) && $data["password"]){
                    $entityManager = $this->getDoctrine()->getManager();
                    $userToCheck = $entityManager->getRepository("App:User")->findOneByEmail($data["email"]);
                    // Comprobamos que el email no está ya registrado, si lo está se mostrará un error
                    if(!$userToCheck){
                        // Check de seguridad
                        if($user instanceof User && in_array("ROLE_ADMIN",$user->getRoles())){
                            // Creamos el usuario
                            $createUserResponse = $this->userService->registerUser($data["email"], $data["password"]);
                            if(isset($createUserResponse["status"]) && !$createUserResponse["status"]){
                                $response = $createUserResponse;
                            }else{
                                $response = array(
                                    "status" => true,
                                    "message" => "Usuario creado."
                                );
                            }
                        }
                    }else{
                        $response = array(
                            "status" => false,
                            "message" => "El email ya está en uso."
                        );
                    }
                }
            }
        }else{
            $response = $authUser;
        }
        return $response;
    }

    /**
     * Función que habilita un usuario en la plataforma
     */
    public function apiEnableUser($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $authUser = $this->userAuth($data);
        if(isset($authUser["status"]) && $authUser["status"]){
            $user = $authUser["user"];
            $data["user"] = $user;
            if(isset($data["userToEnable"]) && $data["userToEnable"]){
                $entityManager = $this->getDoctrine()->getManager();
                $userToEnable = $entityManager->getRepository("App:User")->findOneByEmail($data["userToEnable"]);
                if($userToEnable){
                    // Check de seguridad
                    if($user instanceof User && in_array("ROLE_ADMIN",$user->getRoles())){
                        if($user != $userToEnable){
                            $response = $this->userService->enableUser($userToEnable);
                        }
                    }
                }
            }
        }else{
            $response = $authUser;
        }
        return $response;
    }

    /**
     * Función que habilita un usuario en la plataforma
     */
    public function apiDisableUser($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $authUser = $this->userAuth($data);
        if(isset($authUser["status"]) && $authUser["status"]){
            $user = $authUser["user"];
            $data["user"] = $user;
            if(isset($data["userToDisable"]) && $data["userToDisable"]){
                $entityManager = $this->getDoctrine()->getManager();
                $userToDisable = $entityManager->getRepository("App:User")->findOneByEmail($data["userToDisable"]);
                if($userToDisable){
                    // Check de seguridad
                    if($user instanceof User && in_array("ROLE_ADMIN",$user->getRoles())){
                        if($user != $userToDisable){
                            $response = $this->userService->disableUser($userToDisable);
                        }else{
                            $response = array(
                                "status" => false,
                                "message" => "No puedes deshabilitar a tu usuario."
                            );
                        }
                    }
                }
            }
        }else{
            $response = $authUser;
        }
        return $response;
    }

    /**
     * Función que obtiene los usuarios de la plataforma
     */
    public function apiGetUsers($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $authUser = $this->userAuth($data);
        if(isset($authUser["status"]) && $authUser["status"]){
            $user = $authUser["user"];
            $data["user"] = $user;
            // Check de seguridad
            if($user instanceof User && in_array("ROLE_ADMIN",$user->getRoles())){
                // Obtengo em
                $entityManager = $this->getDoctrine()->getManager();
                // Obtengo el repositorio de users
                $userRepo = $entityManager->getRepository("App:User");
                if(isset($data["datatable"]) && $data["datatable"]){
                    $dataResponse = $userRepo->getUsersDataTable($data);
                    $response = $dataResponse;
                }else{
                    $dataResponse = $userRepo->getUsers($data);
                    $response = array(
                        "status" => true,
                        "data" => $dataResponse
                    );
                }
            }
        }else{
            $response = $authUser;
        }
        return $response;
    }

    /**
     * Función que obtiene los tokens de un usuario
     */
    public function apiGetTokens($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $authUser = $this->userAuth($data);
        if(isset($authUser["status"]) && $authUser["status"]){
            $user = $authUser["user"];
            $data["user"] = $user;
            // Obtengo em
            $entityManager = $this->getDoctrine()->getManager();
            // Obtengo el repositorio de UserToken
            $userTokenRepo = $entityManager->getRepository("App:UserToken");
            if(isset($data["datatable"]) && $data["datatable"]){
                $dataResponse = $userTokenRepo->getUserTokensDataTable($data);
                $response = $dataResponse;
            }else{
                $dataResponse = $userTokenRepo->getUserTokens($data);
                $response = array(
                    "status" => true,
                    "data" => $dataResponse
                );
            }
        }else{
            $response = $authUser;
        }
        return $response;
    }

    /**
     * Función que crea user token
     */
    public function createUserToken($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $authUser = $this->userAuth($data);
        if(isset($authUser["status"]) && $authUser["status"]){
            $user = $authUser["user"];
            $token = uniqid().uniqid();
            $newUserToken = new UserToken();
            $newUserToken->setUser($user);
            $newUserToken->setLastUseDate(new \DateTime());
            $newUserToken->setToken($token);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newUserToken);
            $entityManager->flush($newUserToken);
            $response = array(
                "status" => true,
                "token" => $token
            );
        }else{
            $response = $authUser;
        }
        return $response;
    }

    /**
     * Función que elimina user token
     */
    public function deleteUserToken($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        $authUser = $this->userAuth($data);
        if(isset($authUser["status"]) && $authUser["status"]){
            $user = $authUser["user"];
            if(isset($data["userTokenToDelete"]) && $data["userTokenToDelete"]){
                $userToken = $data["userTokenToDelete"];
                $entityManager = $this->getDoctrine()->getManager();
                $userToken = $entityManager->getRepository("App:UserToken")->findOneBy(array(
                    "token" => $userToken,
                    "deleted" => 0
                ));
                if($userToken){
                    $auxUser = $userToken->getUser();
                    if($auxUser){
                        if($auxUser == $user){
                            $userToken->setDeleted(1);
                            $entityManager->flush($userToken);
                            $response = array(
                                "status" => true,
                                "message" => "Token borrado."
                            );
                        }else{
                            $response = array(
                                "status" => false,
                                "message" => "No tienes permiso."
                            );
                        }
                    }else{
                        $response = array(
                            "status" => false,
                            "message" => "El usuario del token a eliminar no existe."
                        );
                    }
                }else{
                    $response = array(
                        "status" => false,
                        "message" => "El token a elimiar no existe."
                    );
                }
            }else{
                $response = array(
                    "status" => false,
                    "message" => "El token a eliminar no puede estar vacío."
                );
            }
        }else{
            $response = $authUser;
        }
        return $response;
    }

    /**
     * Función auxiliar que comprueba un token
     */
    public function checkToken($data){
        // Respuesta por defecto
        $response = array(
            "status" => false,
            "message" => "Error inesperado, prueba más tarde."
        );
        if( isset($data["userToken"]) && $data["userToken"] ){
            $userToken = $data["userToken"];
            $entityManager = $this->getDoctrine()->getManager();
            $userToken = $entityManager->getRepository("App:UserToken")->findOneBy(array(
                "token" => $userToken,
                "deleted" => 0
            ));
            if($userToken){
                $user = $userToken->getUser();
                if($user){
                    $response = array(
                        "status" => true,
                        "user" => $user,
                        "message" => "Acceso concedido."
                    );
                }else{
                    $response = array(
                        "status" => false,
                        "message" => "El usuario del token no existe."
                    );
                }
            }else{
                $response = array(
                    "status" => false,
                    "message" => "El token no existe."
                );
            }
        }else{
            $response = array(
                "status" => false,
                "message" => "Debes introducir token."
            );
        }
        return $response;
    }

}