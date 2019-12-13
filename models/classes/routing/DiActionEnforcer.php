<?php


namespace oat\tao\model\routing;

use oat\oatbox\log\LoggerService;
use oat\tao\model\DIAwareInterface;
use oat\tao\model\http\Controller;
use Psr\Log\LoggerAwareInterface;

class DiActionEnforcer extends ActionEnforcer
{

    protected function getController()
    {
        $controllerClass = $this->getControllerClass();

        if (is_a($controllerClass, DIAwareInterface::class, true)) {
            return $this->buildController($controllerClass);
        }

        return parent::getController();
    }

    /**
     * @param string $controllerClass
     * @return mixed
     * @throws \Exception
     */
    private function buildController($controllerClass)
    {
        $containerBuilder = $this->getServiceLocator();

        /** @var Controller|DIAwareInterface $controller */
        $controller = $containerBuilder->get($controllerClass);

        $controller->setRequest($this->getRequest());
        $controller->setResponse($this->getResponse());

//        if ($controller instanceof LoggerAwareInterface) {
//            $controller->setLogger($containerBuilder->get(LoggerService::SERVICE_ID));
//        }
//
        return $controller;
    }

    protected function verifyAuthorization()
    {
        //temporary empty
    }

}
