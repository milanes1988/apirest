<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/12/17
 * Time: 22:45
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
}