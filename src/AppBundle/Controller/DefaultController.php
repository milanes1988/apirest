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
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    public function loginAction(Request $request)
    {
        echo 'LOGIN'; exit;
    }

    public function testAction(Request $request)
    {
        $helpers = $this->get('app-helpers');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackBundle\Entity\User')->findAll();
        return $helpers->serializerJson($user);

    }
}
