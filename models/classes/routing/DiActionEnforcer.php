<?php


namespace oat\tao\model\routing;

use oat\tao\model\DIAwareInterface;
use oat\tao\model\http\Controller;

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

        return $controller;
    }

    protected function verifyAuthorization()
    {
        //temporary empty
    }

}
