<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 24.02.16
 * Time: 17:49
 */

namespace AppBundle\Service;


use AppBundle\Entity\Product;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Bridge\Doctrine\RegistryInterface;
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

            return $this->createErrorView(
                'id',
                $id,
                "Product with id $id not found"
            );
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

            $error = ['message' => 'No products found'];
            $view = View::create();
            $view->setData($error)
                ->setStatusCode(400)
                ->setFormat('json');

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

            return $this->createErrorView(
                'id',
                $id,
                "Product with id $id not found"
            );
        }
        $em = $this->doctrine->getManager();

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

                return $this->createErrorView(
                    'category',
                    $categoryName,
                    'Category not exists'
                );
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

            return $this->createValidationErrorsView($errors);
        }
    }

    public function create(ParamFetcher $paramFetcher)
    {
        $entity = new Product();
        $em = $this->doctrine->getManager();

        $name = $paramFetcher->get('name');
        $price = $paramFetcher->get('price');
        $description = $paramFetcher->get('description');
        $categoryName = $paramFetcher->get('category');
        $category = $em->getRepository('AppBundle:Category')
            ->findOneBy(['name' => $categoryName]);

        if (!$category) {

            return $this->createErrorView(
                'category',
                $categoryName,
                'Category not exists'
            );
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

            return $this->createValidationErrorsView($errors);
        }
    }

    public function remove($id)
    {
        $entity = $this->doctrine
            ->getRepository('AppBundle:Product')
            ->findOneById($id);

        if (!$entity) {
            return $this->createErrorView(
                'id',
                $id,
                "Product with id $id not found"
            );
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