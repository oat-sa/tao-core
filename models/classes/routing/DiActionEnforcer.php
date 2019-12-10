<?php


namespace oat\tao\model\routing;


use oat\oatbox\log\LoggerService;
use oat\tao\model\di\ContainerBuilder;
use oat\tao\model\DIAwareInterface;
use oat\tao\model\http\Controller;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zend\ServiceManager\ServiceLocatorInterface;

class DiActionEnforcer extends ActionEnforcer
{
    /**
     * @var ContainerBuilder
     */
    private $diContainer;

    public function __construct($extensionId, $controller, $action, array $parameters)
    {
        parent::__construct($extensionId, $controller, $action, $parameters);
        $this->diContainer = $this->getDiContainer();

        $this->setLogger($this->diContainer->get(LoggerService::class));
    }


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

        $containerBuilder = $this->getDiContainer();

        /** @var Controller|DIAwareInterface $controller */
        $controller = $containerBuilder->get($controllerClass);

        $controller->setRequest($this->getRequest());
        $controller->setResponse($this->getResponse());

        return $controller;
    }

    private function getDiContainer()
    {
        if (!$this->diContainer) {
            $containerBuilder = new ContainerBuilder();

            $loaderResolver = new LoaderResolver(
                [
                    new YamlFileLoader($containerBuilder, new FileLocator(CONFIG_PATH . 'tao'))]
            // new LegacyTaoServiceLocatorLoader($containerBuilder)
            );

            $delegatingLoader = new DelegatingLoader($loaderResolver);
            $delegatingLoader->load('yml/services.yaml');

            $containerBuilder->compile();
            $this->diContainer = $containerBuilder;
        }

        return $this->diContainer;
    }

//    protected function verifyAuthorization()
//    {
//        //temporary empty
//    }

    public function getServiceLocator()
    {
        // dirty wrapper for POC
        return $this->getDiContainer();
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {

    }
}