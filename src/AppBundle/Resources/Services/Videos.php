<?php

namespace AppBundle\Resources\Services;

use Symfony\Component\HttpFoundation\Request;
use BackBundle\Entity\Video;

class Videos
{
    protected $manager;
    protected $helpers;
    protected $jwtAuth;
    protected $validator;
    protected $arraySucces;
    protected $arrayError;
    protected $knpPaginator;

    public function __construct($manager, $validator, $helpers, $jwtAuth, $knpPag)
    {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->helpers = $helpers;
        $this->jwtAuth = $jwtAuth;
        $this->knpPaginator = $knpPag;
        $this->arraySucces = ['status' => 'Success', 'code' => '200', 'data' => ''];
        $this->arrayError = ['status' => 'Error', 'code' => '400', 'data' => ''];
    }

    public function newVideo(Request $request)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        if ($authCheck) {
            if (null !== $json) {
                $params = \json_decode($json);
                $identityUser = $this->jwtAuth->authCheck($hash, true);

                $createAt = new \DateTime();
                $updateAt = new \DateTime();
                $imagen = null;
                $pathVideo = null;

                $userId = (isset($identityUser->sub)) ? $identityUser->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if (null !== $userId && null !== $title) {
                    $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
                        [
                            'id' => $userId,
                        ]
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
                        [
                            'user' => $user,
                            'title' => $title,
                            'status' => $status,
                            'createdAt' => $createAt,
                        ]
                    );

                    $this->arraySucces['data'] = $newVideo;

                    return $this->helpers->serializerJson($this->arraySucces);
                } else {
                    $this->arrayError['data'] = 'Video not created. Please try again.';

                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = 'Json contain format invalid. Please try again.';

                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'Token is not valid. Please try again.';

            return $this->helpers->serializerJson($this->arrayError);
        }
    }

    public function editVideo(Request $request, $videoId)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        if ($authCheck) {
            if (null !== $json) {
                $params = \json_decode($json);
                $identityUser = $this->jwtAuth->authCheck($hash, true);

                $updateAt = new \DateTime();
                $imagen = null;
                $pathVideo = null;

                $userId = (isset($identityUser->sub)) ? $identityUser->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if (null !== $userId && null !== $title) {
                    $video = $this->manager->getRepository('BackBundle:Video')->findOneBy(
                        [
                            'id' => $videoId,
                        ]
                    );

                    if (isset($identityUser->sub) && $identityUser->sub === $video->getUser()->getId()) {
                        $video->setTitle($title);
                        $video->setDescription($description);
                        $video->setStatus($status);
                        $video->setUpdatedAt($updateAt);

                        $this->manager->persist($video);
                        $this->manager->flush();

                        $this->arraySucces['data'] = 'Video <<'.$title.'>> is udpated.';

                        return $this->helpers->serializerJson($this->arraySucces);
                    } else {
                        $this->arrayError['data'] = 'Not permision is the video. Please try again.';

                        return $this->helpers->serializerJson($this->arrayError);
                    }
                } else {
                    $this->arrayError['data'] = 'Video not update. Please try again.';

                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = 'Json contain format invalid. Please try again.';

                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'Token is not valid. Please try again.';

            return $this->helpers->serializerJson($this->arrayError);
        }
    }

    public function uploadVideo(Request $request, $videoId)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        if ($authCheck) {
            $params = \json_decode($json);
            $identityUser = $this->jwtAuth->authCheck($hash, true);

            $video = $this->manager->getRepository('BackBundle:Video')->findOneBy(
                [
                    'id' => $videoId,
                ]
            );

            if (null !== $videoId && isset($identityUser->sub) && $identityUser->sub === $video->getUser()->getId()) {
                $flagImg = false;
                $flagVideo = false;
                $imageFile = $request->files->get('image', null);
                $videoFile = $request->files->get('video', null);
                $path = 'uploads/videos/';

                if (null !== $imageFile && !empty($imageFile)) {
                    $ext = $imageFile->guessExtension();
                    if ('png' === $ext || 'jpeg' === $ext || 'jpg' === $ext) {
                        $fileName = \time().'.'.$ext;
                        $pathFile = $path.'images/video_'.$videoId;
                        $imageFile->move($pathFile, $fileName);

                        $video->setImage($fileName);
                        $flagImg = true;
                    } else {
                        $this->arrayError['data'] = 'This image fomat not valid. Please try again.';

                        return $this->helpers->serializerJson($this->arrayError);
                    }
                }

                if (null !== $videoFile && !empty($videoFile)) {
                    $ext = $videoFile->guessExtension();
                    if ('avi' === $ext || 'mp4' === $ext) {
                        $fileName = \time().'.'.$ext;
                        $pathFile = $path.'files/video_'.$videoId;
                        $videoFile->move($pathFile, $fileName);

                        $video->setVideoPath($fileName);
                        $flagVideo = true;
                    } else {
                        $this->arrayError['data'] = 'This video fomat not valid. Please try again.';

                        return $this->helpers->serializerJson($this->arrayError);
                    }
                }
                if ($flagImg || $flagVideo) {
                    $this->manager->persist($video);
                    $this->manager->flush();

                    $this->arraySucces['data'] = 'File upload for video is success.';

                    return $this->helpers->serializerJson($this->arraySucces);
                } else {
                    $this->arrayError['data'] = 'Error occurred when uploading the files. Please try again.';

                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = 'Not permision is the video. Please try again.';

                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'Token is not valid. Please try again.';

            return $this->helpers->serializerJson($this->arrayError);
        }
    }

    public function listVideo(Request $request)
    {
        $dql = 'SELECT v FROM BackBundle:Video v ORDER BY v.id';
        $query = $this->manager->createQuery($dql);

        $page = $request->query->getInt('page', 1);
        $itemsPage = 6;
        $pagination = $this->knpPaginator->paginate($query, $page, $itemsPage);
        $totalItems = $pagination->getTotalItemCount();

        $data = [
            'status' => 'Success',
            'code' => '200',
            'totalItems' => $totalItems,
            'pageActuality' => $page,
            'itemsPerPage' => $itemsPage,
            'totalPage' => \ceil($totalItems / $itemsPage),
            'data' => $pagination,
        ];

        return $this->helpers->serializerJson($data);
    }

    public function lastVideo(Request $request)
    {
        $dql = 'SELECT v FROM BackBundle:Video v ORDER BY v.createdAt DESC';
        $query = $this->manager->createQuery($dql)->setMaxResults(5);
        $videos = $query->getResult();

        $this->arraySucces['data'] = $videos;

        return $this->helpers->serializerJson($this->arraySucces);
    }

    public function detailVideo(Request $request, $videoId)
    {
        $video = $this->manager->getRepository('BackBundle:Video')->findOneBy(
            [
                'id' => $videoId,
            ]
        );

        $data = $this->arrayError['data'] = 'Video is not finder in aplications';

        if ($video) {
            $data = $this->arraySucces['data'] = $video;
        }

        return $this->helpers->serializerJson($data);
    }

    public function searchVideo(Request $request, $search)
    {
        if (null !== $search) {
            $dql = 'SELECT v FROM BackendBundle:Video v '
                .'WHERE v.title LIKE :search OR '
                .'v.description LIKE :search ORDER BY v.id DESC';
            $query = $this->manager->createQuery($dql)
                ->setParameter('search', "%$search%");
        } else {
            $dql = 'SELECT v FROM BackendBundle:Video v ORDER BY v.id DESC';
            $query = $this->manager->createQuery($dql);
        }

        $page = $request->query->getInt('page', 1);
        $itemsPage = 6;
        $pagination = $this->knpPaginator->paginate($query, $page, $itemsPage);
        $totalItems = $pagination->getTotalItemCount();

        $data = [
            'status' => 'Success',
            'code' => '200',
            'totalItems' => $totalItems,
            'pageActuality' => $page,
            'itemsPerPage' => $itemsPage,
            'totalPage' => \ceil($totalItems / $itemsPage),
            'data' => $pagination,
        ];

        return $this->helpers->serializerJson($data);
    }
}
