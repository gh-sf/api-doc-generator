<?php

namespace AppBundle\Controller\api;

use AppBundle\Entity\Role;
use AppBundle\Form\AddRole;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class RoleController extends Controller
{
    /**
     * @Route("/api/roles", name="get-roles")
     *
     * @Method("GET")
     */
    public function getRolesAction(Request $request)
    {
        $roles = $this->getDoctrine()->getRepository('AppBundle:Role')->findAll();

        return new JsonResponse(['roles' => $roles]);
    }

    /**
     * @Route("/api/role/{id}", name="get-role-id")
     * @param int $id
     * @return JsonResponse
     * @Method("GET")
     */
    public function getRoleAction($id)
    {
        $role = $this->getDoctrine()->getRepository('AppBundle:Role')->find($id);

        return new JsonResponse(['role' => $role]);
    }

    /**
     * @Route("/api/add-role", name="add-role")
     * @Method("PUT")
     */
    public function addRoleAction(Request $request)
    {
        $role = new Role();

        $form = $this->createForm(AddRole::class, $role, ["method" => "PUT"]);

        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($role);
            $em->flush();
            return new JsonResponse($role);
        }

        return new JsonResponse([
            'message' => $form->getErrors(true,true)
        ]);

    }

    /**
     * @Route("/api/role/{id}", name="update-role-id")
     * @param int $id
     * @return JsonResponse
     * @Method("POST")
     */
    public function updateRoleAction($id, Request $request)
    {
        $role = $this->getDoctrine()->getRepository('AppBundle:Role')->find($id);

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(AddRole::class, $role);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            return new JsonResponse($role);
        }else{
            return new JsonResponse([
                'message' => $form->getErrors(true,true)
            ]);
        }

    }

    /**
     * @Route("/api/role/{id}", name="remove-role-id")
     * @param int $id
     * @return JsonResponse
     * @Method("DELETE")
     */
    public function removeRoleAction($id, Request $request)
    {
        $role = $this->getDoctrine()->getRepository('AppBundle:Role')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($role);
        $em->flush();

        return new JsonResponse([
            'message' => 'Role delete!!!'
        ]);


    }
}