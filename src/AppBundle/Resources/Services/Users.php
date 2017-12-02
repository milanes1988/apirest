<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/12/17
 * Time: 1:05
 */

namespace AppBundle\Resources\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackBundle\Entity\User;

class Users
{
    protected $manager;
    protected $helpers;
    protected $jwtAuth;
    protected $validator;
    protected $arraySucces;
    protected $arrayError;

    public function __construct($manager, $validator, $helpers, $jwtAuth)
    {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->helpers = $helpers;
        $this->jwtAuth = $jwtAuth;
        $this->arraySucces = array('status' => 'Success' ,'code' => '200', 'data' => '');
        $this->arrayError = array('status' => 'Success' ,'code' => '400', 'data' => '');

    }

    public function createUser(Request $request)
    {
        $json = $request->get('json', null);

        $fieldsConstain = new Assert\NotBlank();
        $fieldsConstain->message = 'Json contain format invalid. Please try again.';
        $validateJson = $this->validator->validate($json, $fieldsConstain);

        if (count($validateJson) == 0) {
            $params = json_decode($json);

            $createAt = new \DateTime();
            $image = null;
            $role = 'user';
            $email = (isset($params->email)) ? $params->email : null;
            $name = (isset($params->name)) ? $params->name : null;
            $surname = (isset($params->surname)) ? $params->surname : null;
            $pass = (isset($params->pass)) ? $params->pass : null;

            $mailConstain = new Assert\Email();
            $mailConstain->message = 'This email not valid. Plis insert email valid.';
            $validateEmail = $this->validator->validate($email, $mailConstain);

            if (count($validateEmail) == 0) {
                if ($pass != null && $name != null && $surname != null) {

                    $user = new User();
                    $user->setCreatedAt($createAt);
                    $user->setImage($image);
                    $user->setRole($role);

                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    $pwd = hash('sha256', $pass);
                    $user->setPassword($pwd);

                    $findUser = $this->manager->getRepository('BackBundle:User')->findBy(
                        array(
                            'email' => $email
                        )
                    );

                    if (count($findUser) == 0) {
                        $this->manager->persist($user);
                        $this->manager->flush();
                        $this->arraySucces['data'] = 'The user <<' . $name . ' ' . $surname . '>> is create successful';
                        return $this->helpers->serializerJson($this->arraySucces);
                    } else {
                        $this->arrayError['data'] = 'The email -' . $email . '- is used for accounts. Please try other email';
                        return $this->helpers->serializerJson($this->arrayError);
                    }
                } else {
                    $this->arrayError['data'] = 'Data invalid. Please try again';
                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = $mailConstain->message;
                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = $fieldsConstain->message;
            return $this->helpers->serializerJson($this->arrayError);
        }
    }


    public function editUser(Request $request)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        $fieldsConstain = new Assert\NotBlank();
        $fieldsConstain->message = 'Json contain format invalid. Please try again.';
        $validateJson = $this->validator->validate($json, $fieldsConstain);

        if ($authCheck) {
            if (count($validateJson) == 0) {
                $params = json_decode($json);

                $identityUser = $this->jwtAuth->authCheck($hash, true);

                $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
                    array(
                        'id' => $identityUser->sub
                    )
                );

                $createAt = new \DateTime();
                $image = null;
                $role = 'user';
                $email = (isset($params->email)) ? $params->email : null;
                $name = (isset($params->name)) ? $params->name : null;
                $surname = (isset($params->surname)) ? $params->surname : null;
                $pass = (isset($params->pass)) ? $params->pass : null;

                $mailConstain = new Assert\Email();
                $mailConstain->message = 'This email not valid. Plis insert email valid.';
                $validateEmail = $this->validator->validate($email, $mailConstain);

                if (count($validateEmail) == 0) {
                    if ($name != null && $surname != null) {

                        $user->setCreatedAt($createAt);
                        $user->setImage($image);
                        $user->setRole($role);

                        $user->setEmail($email);
                        $user->setName($name);
                        $user->setSurname($surname);

                        if ($pass != null) {
                            $pwd = hash('sha256', $pass);
                            $user->setPassword($pwd);
                        }

                        $findUser = $this->manager->getRepository('BackBundle:User')->findOneBy(
                            array(
                                'email' => $email
                            )
                        );

                        if (count($findUser) == 0 || $findUser->getEmail() == $identityUser->email) {
                            $this->manager->persist($user);
                            $this->manager->flush();
                            $this->arraySucces['data'] = 'The user <<' . $user->getName() . ' ' . $user->getSurname() . '>> is update successful';
                            return $this->helpers->serializerJson($this->arraySucces);
                        } else {
                            $this->arrayError['data'] = 'The email -' . $email . '- is used for accounts. Please try other email';
                            return $this->helpers->serializerJson($this->arrayError);
                        }
                    } else {
                        $this->arrayError['data'] = 'Data invalid. Please try again';
                        return $this->helpers->serializerJson($this->arrayError);
                    }
                } else {
                    $this->arrayError['data'] = $mailConstain->message;
                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = $fieldsConstain->message;
                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'Token is not valid. Please try again';
            return $this->helpers->serializerJson($this->arrayError);
        }
    }


    public function uploadImage(Request $request)
    {

        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        $fieldsConstain = new Assert\NotBlank();
        $fieldsConstain->message = 'Json contain format invalid. Please try again.';
        $validateJson = $this->validator->validate($json, $fieldsConstain);

        if ($authCheck) {
            $params = json_decode($json);
            $identityUser = $this->jwtAuth->authCheck($hash, true);

            $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
                array(
                    'id' => $identityUser->sub
                )
            );

            $file = $request->files->get('image');

            if (isset($file) && $file != null) {
                $ext = $file->guessExtension();
                if ($ext == 'png' ||  $ext == 'jpeg' || $ext == 'jpg'){

                    $fileName = time() . '.' . $ext;
                    $file->move('uploads/users', $fileName);

                    $user->setImage($fileName);
                    $this->manager->persist($user);
                    $this->manager->flush();
                    $this->arraySucces['data'] = 'The user <<' . $user->getName() . ' ' . $user->getSurname() . '>> is image upload';
                    return $this->helpers->serializerJson($this->arraySucces);
                }else{
                    $this->arrayError['data'] = 'This image fomat not valid. Please try again.';
                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = 'Image not uploaded, Please try again.';
                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'Token is not valid. Please try again.';
            return $this->helpers->serializerJson($this->arrayError);
        }
    }

}