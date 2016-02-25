<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
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
    public function cgetProductAction()
    {
        $view = $this->get('app.product_manager')->findAll();

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Get("/api/v1/products/{id}")
     */
    public function getProductAction($id)
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
     * @RequestParam(name="price", nullable=true, strict=true, description="Product price")
     * @RequestParam(name="category", nullable=true, strict=true, description="Product category")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putProductAction($id, ParamFetcher $paramFetcher)
    {
        $view = $this->get('app.product_manager')->update($id, $paramFetcher);

        return $this->handleView($view);
    }

    /**
     * @param ParamFetcher $paramFetcher
     * @Post("/api/v1/products")
     *
     * @RequestParam(name="name", nullable=false, strict=true, description="Product name")
     * @RequestParam(name="description", nullable=false, strict=true, description="Product description")
     * @RequestParam(name="price", nullable=false, strict=true, description="Product price")
     * @RequestParam(name="category", nullable=false, strict=true, description="Product category")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postProductAction(ParamFetcher $paramFetcher)
    {
        $view = $this->get('app.product_manager')->create($paramFetcher);

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @Delete("/api/v1/products/{id}")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteProductAction($id)
    {
        $view = $this->get('app.product_manager')->remove($id);

        return $this->handleView($view);
    }
}
