<?php

namespace AppBundle\Controller\api;

use AppBundle\Entity\Role;
use AppBundle\Form\AddUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class UserController extends Controller
{
    /**
     * @Route("/api/users", name="get-users")
     *
     * @Method("GET")
     */
    public function getUsersAction(Request $request)
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();

        return new JsonResponse(['users' => $users]);
    }

    /**
     * @Route("/api/user/{id}", name="get-user-id")
     * @param int $id
     * @return JsonResponse
     * @Method("GET")
     */
    public function getUserAction($id)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        return new JsonResponse(['user' => $user]);
    }

    /**
     * @Route("/api/registration", name="registration")
     * @Method("PUT")
     */
    public function registrationAction(Request $request)
    {
        $user = new User();
        $role = $this->getDoctrine()->getRepository('AppBundle:Role')->find(40);
        /**
         * @var Role $role
         * */
        $user->addRole($role);

        $form = $this->createForm(AddUser::class, $user, ["method" => "PUT"]);

        $em = $this->getDoctrine()->getManager();


            $form->handleRequest($request);
            if ($form->isValid()) {
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $user->setPassword($encoder->encodePassword( $user->getPlainPassword(), $user->getSalt()));

                $em->persist($user);
                $em->flush();
                return new JsonResponse($user);
            }

        $errors = $form->getErrors(true,true);
                return new JsonResponse([
                    'message' => (string) $form->getErrors(true,true)
                ]);

    }

    /**
     * @Route("/api/user/{id}", name="update-user-id")
     * @param int $id
     * @return JsonResponse
     * @Method("POST")
     */
    public function updateUserAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(AddUser::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            return new JsonResponse($user);
        }else{
            return new JsonResponse([
                'message' => $form->getErrors(true,true)
            ]);
        }

    }

    /**
     * @Route("/api/user/{id}", name="remove-user-id")
     * @param int $id
     * @return JsonResponse
     * @Method("DELETE")
     */
    public function removeUserAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'User delete!!!'
        ]);


    }
}