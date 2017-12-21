<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends Controller
{
    public function newAction(Request $request)
    {
        $service = $this->get('app-comment');

        return $service->newComment($request);
    }

    public function deleteAction(Request $request, $id = null)
    {
        $service = $this->get('app-comment');

        return $service->deleteComment($request, $id);
    }

    public function listAction(Request $request, $id = null)
    {
        $service = $this->get('app-comment');

        return $service->listComment($request, $id);
    }
}
