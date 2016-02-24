<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;

class ProductController extends FOSRestController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Get("/api/v1/products")
     *
     */
    public function cgetAction()
    {
        $data = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->findAll();

        if (!$data) {
            throw $this->createNotFoundException('data not found');
        }

        $view = $this->view($data, 200)
            ->setTemplateVar('products')
            ->setFormat('json');

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Get("/api/v1/products/{id}")
     */
    public function getAction($id)
    {
        $view = $this->get('app.product_manager')->findById($id);

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @param ParamFetcher $paramFetcher
     * @Put("/api/v1/products/{id}")
     *
     * @RequestParam(name="name", nullable=true, strict=true, description="Product name")
     * @RequestParam(name="description", nullable=true, strict=true, description="Product description")
     * @RequestParam(name="price", requirements="\d+", nullable=true, strict=true, description="Product price")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction($id, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Product')
            ->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('product not found');
        }

        if ($name = $paramFetcher->get('name')) {
            $entity->setName($name);
        }
        if ($description = $paramFetcher->get('description')) {
            $entity->setDescription($description);
        }
        if ($price = $paramFetcher->get('price')) {
            $entity->setPrice($price);
        }

        $view = $this->view();

        $errors = $this->get('validator')->validate($entity);
        if (0 == count($errors)) {
            $em->flush();
            $view->setData($entity)
                ->setStatusCode(200)
                ->setFormat('json');
        }

        return $this->handleView($view);

    }
}
