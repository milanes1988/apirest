<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/12/17
 * Time: 22:54
 */

namespace AppBundle\Resources\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackBundle\Entity\Video;

class Videos
{
    protected $manager;
    protected $helpers;
    protected $jwtAuth;
    protected $validator;
    protected $arraySucces;
    protected $arrayError;

    public function __construct($manager, $validator, $helpers, $jwtAuth)
    {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->helpers = $helpers;
        $this->jwtAuth = $jwtAuth;
        $this->arraySucces = array('status' => 'Success' ,'code' => '200', 'data' => '');
        $this->arrayError = array('status' => 'Success' ,'code' => '400', 'data' => '');
    }

    public function newVideo(Request $request){
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        if ($authCheck){
            if($json != null){
                $params = json_decode($json);
                $identityUser = $this->jwtAuth->authCheck($hash, true);

                $createAt = new \DateTime();
                $updateAt = new \DateTime();
                $imagen = null;
                $pathVideo = null;


                $userId = (isset($identityUser->sub)) ? $identityUser->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if($userId != null && $title != null){

                    $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
                        array(
                            'id' => $userId
                        )
                    );

                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setStatus($status);
                    $video->setCreatedAt($createAt);
                    $video->setUpdatedAt($updateAt);

                    $this->manager->persist($video);
                    $this->manager->flush();

                    $newVideo = $this->manager->getRepository('BackBundle:Video')->findOneBy(
                        array(
                            'user' => $user,
                            'title' => $title,
                            'status' => $status,
                            'createdAt' => $createAt
                        )
                    );

                    $this->arraySucces['data'] = $newVideo;
                    return $this->helpers->serializerJson($this->arraySucces);
                }else{
                    $this->arrayError['data'] = 'Video not created. Please try again.';
                    return $this->helpers->serializerJson($this->arrayError);
                }
            }else{
                $this->arrayError['data'] = 'Json contain format invalid. Please try again.';
                return $this->helpers->serializerJson($this->arrayError);
            }
        }else{
            $this->arrayError['data'] = 'Token is not valid. Please try again.';
            return $this->helpers->serializerJson($this->arrayError);
        }
    }



    public function editVideo(Request $request, $videoId){
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        if ($authCheck){
            if($json != null){
                $params = json_decode($json);
                $identityUser = $this->jwtAuth->authCheck($hash, true);

                $updateAt = new \DateTime();
                $imagen = null;
                $pathVideo = null;


                $userId = (isset($identityUser->sub)) ? $identityUser->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if($userId != null && $title != null){


                    $video = $this->manager->getRepository('BackBundle:Video')->findOneBy(
                        array(
                            'id' => $videoId
                        )
                    );

                    if (isset($identityUser->sub) && $identityUser->sub == $video->getUser()->getId()){
                        $video->setTitle($title);
                        $video->setDescription($description);
                        $video->setStatus($status);
                        $video->setUpdatedAt($updateAt);

                        $this->manager->persist($video);
                        $this->manager->flush();

                        $this->arraySucces['data'] = 'Video <<'.$title.'>> is udpated.';
                        return $this->helpers->serializerJson($this->arraySucces);

                    }else{
                        $this->arrayError['data'] = 'Not permision is the video. Please try again.';
                        return $this->helpers->serializerJson($this->arrayError);
                    }
                }else{
                    $this->arrayError['data'] = 'Video not update. Please try again.';
                    return $this->helpers->serializerJson($this->arrayError);
                }
            }else{
                $this->arrayError['data'] = 'Json contain format invalid. Please try again.';
                return $this->helpers->serializerJson($this->arrayError);
            }
        }else{
            $this->arrayError['data'] = 'Token is not valid. Please try again.';
            return $this->helpers->serializerJson($this->arrayError);
        }
    }

}