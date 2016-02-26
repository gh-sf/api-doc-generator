<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 25.02.16
 * Time: 20:39
 */

namespace AppBundle\Controller\Api\v1;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;


class CategoryController extends FOSRestController
{
    /**
     * Returns all categories list
     *
     * @Get("/api/v1/categories")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetCategoryAction()
    {
        $view = $this->get('app.category_manager')->listAll();

        return $this->handleView($view);
    }

    /**
     * Creates a new category
     *
     * @Post("/api/v1/categories")
     *
     * @RequestParam(name="name", nullable=true, strict=true, description="Category name")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCategoryAction(ParamFetcher $paramFetcher)
    {
        $view = $this->get('app.category_manager')->create($paramFetcher);

        return $this->handleView($view);
    }

    /**
     *  Updates category
     *
     * @param $id
     * @param ParamFetcher $paramFetcher
     *
     * @Put("/api/v1/categories/{id}")
     *
     * @RequestParam(name="name", nullable=true, strict=true, description="Category name")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putCategoryAction($id, ParamFetcher $paramFetcher)
    {
        $view = $this->get('app.category_manager')->update($id, $paramFetcher);

        return $this->handleView($view);
    }

    /**
     * Removes category
     *
     * @param $id
     * @Delete("/api/v1/categories/{id}")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCategoryAction($id)
    {
        $view = $this->get('app.category_manager')->remove($id);

        return $this->handleView($view);
    }
}