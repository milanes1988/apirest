<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 30/11/17
 * Time: 9:50
 */

namespace AppBundle\Resources\config\services;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class Helpers
{
    public function serializerJson($data)
    {
        $nomalizer = array(new GetSetMethodNormalizer());
        $encoders = array('json' => new JsonEncoder());

        $serializer = new Serializer($nomalizer, $encoders);
        $jsonData = $serializer->serialize($data, 'json');

        $response = new Response();
        $response->setContent($jsonData);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}