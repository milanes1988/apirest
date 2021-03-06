<?php

namespace AppBundle\Resources\Services;

use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class Helpers
{
    public function serializerJson($data)
    {
        $nomalizer = [new GetSetMethodNormalizer()];
        $encoders = ['json' => new JsonEncoder()];

        $serializer = new Serializer($nomalizer, $encoders);
        $jsonData = $serializer->serialize($data, 'json');

        $response = new Response();
        $response->setContent($jsonData);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function checkToken($jwt, $key, $getIdentity = false)
    {
        $auth = false;

        try {
            $decode = JWT::decode($jwt, $key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (isset($decode->sub)) {
            $auth = true;
        }

        if ($getIdentity) {
            return $decode;
        } else {
            return $auth;
        }
    }
}
