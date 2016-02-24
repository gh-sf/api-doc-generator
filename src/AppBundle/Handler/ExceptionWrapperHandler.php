<?php
/**
 * Created by PhpStorm.
 * User: fumus
 * Date: 24.02.16
 * Time: 16:52
 */

namespace AppBundle\Handler;


use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use FOS\RestBundle\Util\ExceptionWrapper;

class ExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{

    public function wrap($data)
    {
        return new ExceptionWrapper($data);
    }
}