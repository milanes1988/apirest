<?php

namespace AppBundle\Resources\Services;

use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class JwtAuth
{
    protected $manager;
    protected $helpers;
    protected $validator;
    protected $key;
    protected $authCheck;

    public function __construct($manager, $validator, $helpers)
    {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->helpers = $helpers;
        $this->key = "clave-secret";
        $this->authCheck = false;
    }

    public function loginApp(Request $request)
    {
        $json = $request->get('json', null);

        $fieldsConstain = new Assert\NotBlank();
        $fieldsConstain->message = 'Json not valid. Send data format Json for procesing';
        $validateJson = $this->validator->validate($json, $fieldsConstain);
        $arrayError = array('status' => 'Error', 'data' => '');

        if (count($validateJson) == 0) {
            $params = json_decode($json);
            $email = (isset($params->email)) ? $params->email : null;
            $pass = (isset($params->pass)) ? $params->pass : null;
            $hash = (isset($params->hash) && ($params->hash)) ? $params->hash : null;

            $mailConstain = new Assert\Email();
            $mailConstain->message = 'This email not valid. Plis insert email valid.';
            $validateEmail = $this->validator->validate($email, $mailConstain);

            if (count($validateEmail) == 0 && $pass != null) {
                $data = $this->loginUser($email, $pass, $hash);
                return new JsonResponse($data);
            } else {
                $arrayError['data'] = $mailConstain->message;
                return $this->helpers->serializerJson($arrayError);
            }
        } else {
            $arrayError['data'] = $fieldsConstain->message;
            return $this->helpers->serializerJson($arrayError);
        }
    }


    public function loginUser($email, $pass, $getHash = null)
    {
        $login = false;
        $pwd = hash('sha256', $pass);

        $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
            array(
                'email' => $email,
                'password' => $pwd
            )
        );

        if (is_object($user))
            $login = true;


        if ($login) {
            $token = array(
                'sub' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'pass' => $user->getPassword(),
                'image' => $user->getImage(),
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, array('HS256'));

            if($getHash)
                return $jwt;
            else
                return $decode;


            return array('status' => 'Succes', 'data' => $user);
        } else
            return array('status' => 'Error', 'data' => 'Incorrect Login. Please try again!');

    }

    public function authCheck($hash, $getIdentity = false){

        if ($hash != null){
            if (!$getIdentity){
                $checkToken = $this->helpers->checkToken($hash, $this->key);
            }else{
                $checkToken = $this->helpers->checkToken($hash, $this->key, true);
            }

            if ($checkToken)
                $this->authCheck = true;
            if (is_object($checkToken))
                $this->authCheck = $checkToken;
        }

        return $this->authCheck;
    }
}