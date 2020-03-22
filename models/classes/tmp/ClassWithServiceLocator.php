<?php

declare(strict_types=1);

namespace oat\tao\model\tmp;

use oat\oatbox\session\SessionService;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClassWithServiceLocator
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * ServiceManager automatically autowired
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getSession(): SessionService
    {
        return $this->serviceLocator->get(SessionService::SERVICE_ID);
    }
}
