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
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductManager
{
    protected $doctrine;
    protected $validator;

    public function __construct(RegistryInterface $doctrine, ValidatorInterface $validator)
    {
        $this->doctrine = $doctrine;
        $this->validator = $validator;

    }

    public function findById($id)
    {
        $entity = $this->doctrine
            ->getRepository('AppBundle:Product')
            ->findOneById($id);

        if (!$entity) {
            $exception = new NotFoundHttpException("Product with id $id not found");
            $view = $this->createExceptionView($exception);

            return $view;
        }
        $view = View::create();
        $view->setData($entity)->setStatusCode(200)->setFormat('json');

        return $view;
    }

    public function findAll()
    {
        $products = $this->doctrine
            ->getRepository('AppBundle:Product')
            ->findAllWithDependencies();

        if (!$products) {
            $exception = new NotFoundHttpException('None product was found');
            $view = $this->createExceptionView($exception);

            return $view;
        }
        $view = View::create();
        $view->setData($products)->setStatusCode(200)->setFormat('json');

        return $view;

    }

    public function update($id, ParamFetcher $paramFetcher)
    {
        $entity = $this->doctrine->getRepository('AppBundle:Product')
            ->findOneById($id);

        if (!$entity) {
            $exception = new NotFoundHttpException("Product with id $id not found");
            $view = $this->createExceptionView($exception);

            return $view;
        }
        $em = $this->doctrine->getEntityManager();

        if ($name = $paramFetcher->get('name')) {
            $entity->setName($name);
        }

        if ($description = $paramFetcher->get('description')) {
            $entity->setDescription($description);
        }

        if ($price = $paramFetcher->get('price')) {
            $entity->setPrice($price);
        }

        if ($categoryName = $paramFetcher->get('category')) {
            $categoryName = strip_tags($categoryName);

            $category = $em->getRepository('AppBundle:Category')
                ->findOneBy(['name' => $categoryName]);

            if (null !== $category) {
                $entity->setCategory($category);
            } else {

                $categoryError = [
                    'property' => 'category',
                    'invalid_value' => $categoryName,
                    'message' => 'Category not exists',
                ];

                $view = View::create();
                $view->setData($categoryError)
                    ->setStatusCode(400)
                    ->setFormat('json');

                return $view;
            }
        }

        $view = View::create();

        $errors = $this->validator->validate($entity);

        if (0 == count($errors)) {
            $em->flush();
            $view->setStatusCode(204)
                ->setFormat('json');

            return $view;

        } else {
            $count = 0;
            foreach ($errors as $error) {
                $errorData[$count]['property'] = $error->getPropertyPath();
                $errorData[$count]['invalid_value'] = $error->getInvalidValue();
                $errorData[$count]['message'] = $error->getMessage();
                $count++;
            }

            $view->setData($errorData)
                ->setStatusCode(400)
                ->setFormat('json');

            return $view;
        }
    }

    public function create(ParamFetcher $paramFetcher)
    {
        $entity = new Product();
        $em = $this->doctrine->getEntityManager();

        $name = $paramFetcher->get('name');
        $price = $paramFetcher->get('price');
        $description = $paramFetcher->get('description');
        $categoryName = $paramFetcher->get('category');
        $category = $em->getRepository('AppBundle:Category')
            ->findOneBy(['name' => $categoryName]);

        if (!$category) {
            $categoryError = [
                'property' => 'category',
                'invalid_value' => $categoryName,
                'message' => 'Category not exists',
            ];

            $view = View::create();
            $view->setData($categoryError)
                ->setStatusCode(400)
                ->setFormat('json');

            return $view;
        }

        $entity->setName(strip_tags($name));
        $entity->setDescription(strip_tags($description));
        $entity->setPrice($price);
        $entity->setCategory($category);

        $view = View::create();

        $errors = $this->validator->validate($entity);

        if (0 == count($errors)) {
            $em->persist($entity);
            $em->flush();
            $view->setStatusCode(204)
                ->setFormat('json');

            return $view;

        } else {
            $count = 0;
            foreach ($errors as $error) {
                $errorData[$count]['property'] = $error->getPropertyPath();
                $errorData[$count]['invalid_value'] = $error->getInvalidValue();
                $errorData[$count]['message'] = $error->getMessage();
                $count++;
            }

            $view->setData($errorData)
                ->setStatusCode(400)
                ->setFormat('json');

            return $view;
        }


    }

    public function remove($id)
    {
        $entity = $this->doctrine
            ->getRepository('AppBundle:Product')
            ->findOneById($id);

        if (!$entity) {
            $exception = new NotFoundHttpException("Product with id $id not found");
            $view = $this->createExceptionView($exception);

            return $view;
        }

        $em = $this->doctrine->getManager();
        $em->remove($entity);
        $em->flush();

        $view = View::create();
        $view->setStatusCode(204)
            ->setFormat('json');

        return $view;
    }


    private function createExceptionView($exception, $errors = null)
    {
        $data = array();
        $data['status_code'] = $exception->getStatusCode();
        $data['message'] = $exception->getMessage();

        if ($errors) {
            $data['errors'] = $errors;
        }

        $handler = new ExceptionWrapperHandler();
        $array = $handler->wrap($data);

        $view = View::create();
        $view->setData($array)->setStatusCode(400)->setFormat('json');

        return $view;
    }
}