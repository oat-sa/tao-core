<?php


namespace oat\tao\model\routing;

use oat\tao\model\http\Controller;

class DiActionEnforcer extends ActionEnforcer
{

    protected function getController()
    {
        $controllerClass = $this->getControllerClass();
        $containerBuilder = $this->getServiceLocator();

        if ($containerBuilder->has($controllerClass)){
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

        /** @var Controller $controller */
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
