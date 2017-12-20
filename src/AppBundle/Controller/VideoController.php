<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends Controller
{
    public function newAction(Request $request)
    {
        $service = $this->get('app-video');

        return $service->newVideo($request);
    }

    public function editAction(Request $request, $id = null)
    {
        $service = $this->get('app-video');

        return $service->editVideo($request, $id);
    }

    public function uploadAction(Request $request, $id = null)
    {
        $service = $this->get('app-video');

        return $service->uploadVideo($request, $id);
    }

    public function listAction(Request $request)
    {
        $service = $this->get('app-video');

        return $service->listVideo($request);
    }

    public function lastAction(Request $request)
    {
        $service = $this->get('app-video');

        return $service->lastVideo($request);
    }

    public function detailAction(Request $request, $id = null)
    {
        $service = $this->get('app-video');

        return $service->detailVideo($request, $id);
    }

    public function searchAction(Request $request, $search = null)
    {
        $service = $this->get('app-video');

        return $service->searchVideo($request, $search);
    }
}
