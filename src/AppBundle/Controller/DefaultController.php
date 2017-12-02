<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    public function loginAction(Request $request)
    {
        $jwtAuth = $this->get('app-jwtauth');
        return $jwtAuth->loginApp($request);
    }

    public function testAction(Request $request)
    {
        $jwtAuth = $this->get('app-jwtauth');

        $hash = $request->get('authorization', null);
        $check = $jwtAuth->authCheck($hash, true);

        var_dump($check);exit;
//        $helpers = $this->get('app-helpers');
//        $em = $this->getDoctrine()->getManager();
//        $user = $em->getRepository('BackBundle:User')->findAll();
//        return $helpers->serializerJson($user);

    }
}
