<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 24.02.16
 * Time: 17:49
 */

namespace AppBundle\Service;


use AppBundle\Entity\Product;
use AppBundle\Handler\ExceptionWrapperHandler;
use FOS\RestBundle\View\View;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductManager
{
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;

    }

    public function findById($id)
    {
        $entity = $this->doctrine
            ->getRepository('AppBundle:Product')
            ->find($id);

        if (!$entity) {

            $exception = new NotFoundHttpException('product not found');

            $data = array();
            $data['exception'] = $exception;
            $data['status_code'] = $exception->getStatusCode();
            $data['message'] = $exception->getMessage();

            $handler = new ExceptionWrapperHandler();
            $array = $handler->wrap($data);

            $view = View::create();
            $view->setData($array)->setStatusCode(400)->setFormat('json');

            return $view;
        }
        $view = View::create();
        $view->setData($entity)->setStatusCode(200)->setFormat('json');

        return $view;
    }
}