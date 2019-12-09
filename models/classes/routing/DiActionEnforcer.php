<?php


namespace oat\tao\model\routing;


use oat\tao\model\DIAwareInterface;
use oat\tao\model\http\Controller;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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

        $containerBuilder = $this->getDiContainer();

//        $extManager = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
//        $userLocksService = $this->getServiceLocator()->get(UserLocks::SERVICE_ID);
//        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
//        $serviceLocator = $this->getServiceLocator();
//        $sessionService = $this->getServiceLocator()->get(SessionService::SERVICE_ID);


//        $containerBuilder->autowire(get_class($extManager), \common_ext_ExtensionsManager::SERVICE_ID);
//        new ContainerConfigurator()
//        $services->set(\common_ext_ExtensionsManager::class)
//            ->factory([ref(common_ext_ExtensionsManager::class), 'get'])
//            ->args([ref('s')])
//        ;

        /** @var Controller|DIAwareInterface $controller */
        $controller = $containerBuilder->get($controllerClass);

        $controller->setRequest($this->getRequest());
        $controller->setResponse($this->getResponse());

        return $controller;
    }

    private function getDiContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $loaderResolver = new LoaderResolver(
            [
                new YamlFileLoader($containerBuilder, new FileLocator(CONFIG_PATH . 'tao'))]
                // new LegacyTaoServiceLocatorLoader($containerBuilder)
        );

        $delegatingLoader = new DelegatingLoader($loaderResolver);
        $delegatingLoader->load('yml/services.yaml');

        $containerBuilder->compile();

        return $containerBuilder;
    }

    protected function verifyAuthorization()
    {
        //temporary empty
    }
}