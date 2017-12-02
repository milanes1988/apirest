<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/12/17
 * Time: 0:59
 */


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    public function newAction(Request $request)
    {
        $service = $this->get('app-user');
        return $service->createUser($request);
    }

    public function editAction(Request $request)
    {
        $service = $this->get('app-user');
        return $service->editUser($request);
    }

    public function uploadAction(Request $request)
    {
        $service = $this->get('app-user');
        return $service->uploadImage($request);
    }
}