<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 25.02.16
 * Time: 20:32
 */

namespace AppBundle\Service;

use AppBundle\Entity\Category;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CategoryManager
{
    protected $doctrine;
    protected $validator;

    public function __construct(RegistryInterface $doctrine, ValidatorInterface $validator)
    {
        $this->doctrine = $doctrine;
        $this->validator = $validator;

    }

    public function listAll()
    {
        $categories = $this->doctrine
            ->getRepository('AppBundle:Category')
            ->findAll();
        if (!$categories) {

            $error = ['message' => 'None category found'];

            $view = View::create();
            $view->setData($error)
                ->setStatusCode(400)
                ->setFormat('json');

            return $view;
        }
        $view = View::create();
        $view->setData($categories)->setStatusCode(200)->setFormat('json');

        return $view;
    }

    public function create(ParamFetcher $paramFetcher)
    {
        $entity = new Category();
        $em = $this->doctrine->getManager();

        $name = strip_tags($paramFetcher->get('name'));
        $category = $em->getRepository('AppBundle:Category')
            ->findOneBy(['name' => $name]);

        if (null !== $category) {

            return $this->createErrorView(
                'name',
                $name,
                "Category $name already exists"
            );
        }
        $entity->setName($name);
        $errors = $this->validator->validate($entity);
        $view = View::create();

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

    public function update($id, ParamFetcher $paramFetcher)
    {
        $entity = $this->doctrine->getRepository('AppBundle:Category')
            ->find($id);

        if (!$entity) {

            return $this->createErrorView(
                'id',
                $id,
                "Category with id $id not exists"
            );
        }

        $name = strip_tags($paramFetcher->get('name'));
        $entity->setName($name);
        $errors = $this->validator->validate($entity);
        $view = View::create();

        if (0 == count($errors)) {
            $em = $this->doctrine->getManager();
            $em->persist($entity);
            $em->flush();
            $view->setStatusCode(204)
                ->setFormat('json');

            return $view;

        } else {

            return $this->createValidationErrorsView($errors);
        }
    }

    public function remove($id)
    {
        $entity = $this->doctrine
            ->getRepository('AppBundle:Category')
            ->find($id);

        if (!$entity) {
            return $this->createErrorView(
                'id',
                $id,
                "Category with id $id not found"
            );
        }
        $products = $this->doctrine
            ->getRepository('AppBundle:Product')
            ->findBy(['category' => $entity->getId()]);

        if (0 !== $count = count($products)) {
            $error = ['message' => "Can't remove category cause it is not empty. There are $count products assosiated with this category"];

            $view = View::create();
            $view->setData($error)
                ->setStatusCode(409)
                ->setFormat('json');

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

    private function createErrorView($property, $invalidValue, $message)
    {
        $error = [
            'property' => $property,
            'invalid_value' => $invalidValue,
            'message' => $message,
        ];

        $view = View::create();
        $view->setData($error)
            ->setStatusCode(400)
            ->setFormat('json');

        return $view;
    }

    private function createValidationErrorsView($errors)
    {
        $view = View::create();
        foreach ($errors as $error) {

            $errorData[] = array(
                'property' => $error->getPropertyPath(),
                'invalid_value' => $error->getInvalidValue(),
                'message' => $error->getMessage(),
            );
        }
        $view->setData($errorData)
            ->setStatusCode(400)
            ->setFormat('json');

        return $view;
    }
}