<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 04/05/17
 * Time: 16:47
 */

namespace oat\tao\model\mvc\middleware;


use oat\tao\model\mvc\error\ExceptionInterpretor;

class TaoErrorHandler extends AbstractTaoMiddleware
{

    public function __invoke($request, $response, $args)
    {

        $Interpretor = new ExceptionInterpretor();
        $Interpretor->setServiceLocator($this->getServiceLocator());
        return $Interpretor->setResponse($response)->setException($args['exception'])->getResponse()->send();
    }

}