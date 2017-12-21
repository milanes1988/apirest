<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation as Nelmio;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        echo 'ApiRest for aplications Angular2';
        exit;
    }

    /**
     * @Nelmio\ApiDoc(
     *  resource=true,
     *  section="v1 - Query videos",
     *  description="Returns a collection of videos.",
     *  output="html",
     *  parameters={
     *      {"name"="email", "dataType"="string", "required"=true, "description"="Introduce tu correo"},
     *      {"name"="pass", "dataType"="string", "required"=true, "description"="Introduce tu contraseña"},
     *      {"name"="hash", "dataType"="string", "required"=true, "description"="Requiere (true o false)"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      401="Returned when the user is not authorized"
     *  },
     *  views = {"default", "query"}
     * )
     */
    public function loginAction(Request $request)
    {
        $jwtAuth = $this->get('app-jwtauth');

        return $jwtAuth->loginApp($request);
    }
}
