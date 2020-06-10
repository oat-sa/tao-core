<?php

/**
 * example to use Caching Service
 *

use oat\oatbox\service\ServiceFactoryInterface;
use oat\tao\model\resources\SecureResourceCachedService;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\tao\model\resources\SecureResourceService;

return new class implements ServiceFactoryInterface {
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        return new SecureResourceCachedService(
            new SecureResourceService(),
            new oat\tao\model\resources\ValidatePermissionsCacheKeyFactory(),
            new oat\tao\model\resources\GetAllChildrenCacheKeyFactory(),
            'generis/cache',
            null
        );
    }
};

**/
use oat\tao\model\resources\SecureResourceService;

return new SecureResourceService();
