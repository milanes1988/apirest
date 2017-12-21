<?php

namespace AppBundle\Controller;

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

    public function channelAction(Request $request, $id = null)
    {
        $service = $this->get('app-user');

        return $service->channelUser($request, $id = null);
    }
}
