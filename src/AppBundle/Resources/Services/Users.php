<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/12/17
 * Time: 1:05.
 */

namespace AppBundle\Resources\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackBundle\Entity\User;

class Users
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
        $this->arraySucces = ['status' => 'Success', 'code' => '200', 'data' => ''];
        $this->arrayError = ['status' => 'Success', 'code' => '400', 'data' => ''];
    }

    public function createUser(Request $request)
    {
        $json = $request->get('json', null);

        $fieldsConstain = new Assert\NotBlank();
        $fieldsConstain->message = 'Json contain format invalid. Please try again.';
        $validateJson = $this->validator->validate($json, $fieldsConstain);

        if (0 === \count($validateJson)) {
            $params = \json_decode($json);

            $createAt = new \DateTime();
            $image = null;
            $role = 'user';
            $email = (isset($params->email)) ? $params->email : null;
            $name = (isset($params->name)) ? $params->name : null;
            $surname = (isset($params->surname)) ? $params->surname : null;
            $pass = (isset($params->pass)) ? $params->pass : null;

            $mailConstain = new Assert\Email();
            $mailConstain->message = 'This email not valid. Plis insert email valid.';
            $validateEmail = $this->validator->validate($email, $mailConstain);

            if (0 === \count($validateEmail)) {
                if (null !== $pass && null !== $name && null !== $surname) {
                    $user = new User();
                    $user->setCreatedAt($createAt);
                    $user->setImage($image);
                    $user->setRole($role);

                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    $pwd = \hash('sha256', $pass);
                    $user->setPassword($pwd);

                    $findUser = $this->manager->getRepository('BackBundle:User')->findBy(
                        [
                            'email' => $email,
                        ]
                    );

                    if (0 === \count($findUser)) {
                        $this->manager->persist($user);
                        $this->manager->flush();
                        $this->arraySucces['data'] = 'The user <<'.$name.' '.$surname.'>> is create successful';

                        return $this->helpers->serializerJson($this->arraySucces);
                    } else {
                        $this->arrayError['data'] = 'The email -'.$email.'- is used for accounts. Please try other email';

                        return $this->helpers->serializerJson($this->arrayError);
                    }
                } else {
                    $this->arrayError['data'] = 'Data invalid. Please try again';

                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = $mailConstain->message;

                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = $fieldsConstain->message;

            return $this->helpers->serializerJson($this->arrayError);
        }
    }

    public function editUser(Request $request)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        $fieldsConstain = new Assert\NotBlank();
        $fieldsConstain->message = 'Json contain format invalid. Please try again.';
        $validateJson = $this->validator->validate($json, $fieldsConstain);

        if ($authCheck) {
            if (0 === \count($validateJson)) {
                $params = \json_decode($json);

                $identityUser = $this->jwtAuth->authCheck($hash, true);

                $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
                    [
                        'id' => $identityUser->sub,
                    ]
                );

                $createAt = new \DateTime();
                $image = null;
                $role = 'user';
                $email = (isset($params->email)) ? $params->email : null;
                $name = (isset($params->name)) ? $params->name : null;
                $surname = (isset($params->surname)) ? $params->surname : null;
                $pass = (isset($params->pass)) ? $params->pass : null;

                $mailConstain = new Assert\Email();
                $mailConstain->message = 'This email not valid. Plis insert email valid.';
                $validateEmail = $this->validator->validate($email, $mailConstain);

                if (0 === \count($validateEmail)) {
                    if (null !== $name && null !== $surname) {
                        $user->setCreatedAt($createAt);
                        $user->setImage($image);
                        $user->setRole($role);

                        $user->setEmail($email);
                        $user->setName($name);
                        $user->setSurname($surname);

                        if (null !== $pass) {
                            $pwd = \hash('sha256', $pass);
                            $user->setPassword($pwd);
                        }

                        $findUser = $this->manager->getRepository('BackBundle:User')->findOneBy(
                            [
                                'email' => $email,
                            ]
                        );

                        if (0 === \count($findUser) || $findUser->getEmail() === $identityUser->email) {
                            $this->manager->persist($user);
                            $this->manager->flush();
                            $this->arraySucces['data'] = 'The user <<'.$user->getName().' '.$user->getSurname().'>> is update successful';

                            return $this->helpers->serializerJson($this->arraySucces);
                        } else {
                            $this->arrayError['data'] = 'The email -'.$email.'- is used for accounts. Please try other email';

                            return $this->helpers->serializerJson($this->arrayError);
                        }
                    } else {
                        $this->arrayError['data'] = 'Data invalid. Please try again';

                        return $this->helpers->serializerJson($this->arrayError);
                    }
                } else {
                    $this->arrayError['data'] = $mailConstain->message;

                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = $fieldsConstain->message;

                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'Token is not valid. Please try again';

            return $this->helpers->serializerJson($this->arrayError);
        }
    }

    public function uploadImage(Request $request)
    {
        $json = $request->get('json', null);
        $hash = $request->get('authorization', null);
        $authCheck = $this->jwtAuth->authCheck($hash);

        $fieldsConstain = new Assert\NotBlank();
        $fieldsConstain->message = 'Json contain format invalid. Please try again.';
        $validateJson = $this->validator->validate($json, $fieldsConstain);

        if ($authCheck) {
            $params = \json_decode($json);
            $identityUser = $this->jwtAuth->authCheck($hash, true);

            $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
                [
                    'id' => $identityUser->sub,
                ]
            );

            $file = $request->files->get('image');

            if (isset($file) && null !== $file) {
                $ext = $file->guessExtension();
                if ('png' === $ext || 'jpeg' === $ext || 'jpg' === $ext) {
                    $fileName = \time().'.'.$ext;
                    $file->move('uploads/users', $fileName);

                    $user->setImage($fileName);
                    $this->manager->persist($user);
                    $this->manager->flush();
                    $this->arraySucces['data'] = 'The user <<'.$user->getName().' '.$user->getSurname().'>> is image upload';

                    return $this->helpers->serializerJson($this->arraySucces);
                } else {
                    $this->arrayError['data'] = 'This image fomat not valid. Please try again.';

                    return $this->helpers->serializerJson($this->arrayError);
                }
            } else {
                $this->arrayError['data'] = 'Image not uploaded, Please try again.';

                return $this->helpers->serializerJson($this->arrayError);
            }
        } else {
            $this->arrayError['data'] = 'Token is not valid. Please try again.';

            return $this->helpers->serializerJson($this->arrayError);
        }
    }

    public function channelUser(Request $request, $id = null)
    {
        $user = $this->manager->getRepository('BackBundle:User')->findOneBy(
            [
                'id' => $id,
            ]
        );

        $dql = "SELECT v FROM BackBundle:Video v WHERE v.user = $id ORDER BY v.id";
        $query = $this->manager->createQuery($dql);

        $page = $request->query->getInt('page', 1);
        $itemsPage = 6;
        $pagination = $this->knpPaginator->paginate($query, $page, $itemsPage);
        $totalItems = $pagination->getTotalItemCount();

        if (count($user) == 1){
            $data = [
                'status' => 'Success',
                'code' => '200',
                'totalItems' => $totalItems,
                'pageActuality' => $page,
                'itemsPerPage' => $itemsPage,
                'totalPage' => \ceil($totalItems / $itemsPage),
            ];
            $data['data']['videos'] = $pagination;
            $data['data']['user'] = $user;
        }else{
            $data = $this->arrayError['data'] = 'User don\'t exist in aplications';
        }


        return $this->helpers->serializerJson($data);
    }
}
