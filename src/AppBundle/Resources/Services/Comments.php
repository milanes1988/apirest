<?php

namespace AppBundle\Resources\Services;

use Symfony\Component\HttpFoundation\Request;
use BackBundle\Entity\Comment;

class Comments
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

    public function newComment(Request $request)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        if ($authCheck) {
            if (null !== $json) {
                $params = \json_decode($json);
                $identityUser = $this->jwtAuth->authCheck($hash, true);

                $createAt = new \DateTime('now');
                $userId = (isset($identityUser->sub)) ? $identityUser->sub : null;
                $videoId = (isset($params->videoId)) ? $params->videoId : null;
                $body = (isset($params->body)) ? $params->body : null;

                if (null !== $userId && null !== $videoId) {
                    $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
                        [
                            'id' => $userId,
                        ]
                    );
                    $video = $this->manager->getRepository('BackBundle:Video')->findOneBy(
                        [
                            'id' => $videoId,
                        ]
                    );

                    $comment = new Comment();
                    $comment->setUser($user);
                    $comment->setVideo($video);
                    $comment->setBody($body);
                    $comment->setCreatedAt($createAt);

                    $this->manager->persist($comment);
                    $this->manager->flush();

                    $this->arraySucces['data'] = 'Comment is create for user.';

                    return $this->helpers->serializerJson($this->arraySucces);
                } else {
                    $this->arrayError['data'] = 'Comments not create. For this user o video no exist.';

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

    public function deleteComment(Request $request, $id)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        if ($authCheck) {
            if (null !== $json) {
                $params = \json_decode($json);
                $identity = $this->jwtAuth->authCheck($hash, true);

                $userId = (isset($identity->sub)) ? $identity->sub : null;
                $comment = $this->manager->getRepository('BackBundle:Comment')->findOneBy(
                    [
                        'id' => $id,
                    ]
                );

                if (null !== $userId && \is_object($comment)) {
                    if (isset($identity->sub) &&
                        ($identity->sub === $comment->getUser()->getId() ||
                         $identity->sub === $comment->getVideo()->getUser()->getId())) {
                        $this->manager->remove($comment);
                        $this->manager->flush();

                        $this->arraySucces['data'] = 'The comment is delete succesfull for user.';

                        return $this->helpers->serializerJson($this->arraySucces);
                    } else {
                        $this->arrayError['data'] = 'The user does not have permission to delete the video.';

                        return $this->helpers->serializerJson($this->arrayError);
                    }
                } else {
                    $this->arrayError['data'] = 'Comments is not delete. Please try again.';

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

    public function listComment(Request $request, $id)
    {
        $video = $this->manager->getRepository('BackBundle:Video')->findOneBy(
            [
                'id' => $id,
            ]
        );

        if (\is_object($video)) {
            $comments = $this->manager->getRepository('BackBundle:Comment')->findBy(
                [
                    'video' => $video,
                ], ['id' => 'DESC']
            );

            if (\count($comments) >= 1) {
                $this->arraySucces['data'] = $comments;

                return $this->helpers->serializerJson($this->arraySucces);
            } else {
                $this->arrayError['data'] = 'Don not exist, comment for video.';

                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'The video id does not exist or is null. Please try again.';

            return $this->helpers->serializerJson($this->arrayError);
        }
    }
}
