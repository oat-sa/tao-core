<?php
/**
 * Default config header created during install
 */

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ServiceFactoryInterface;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;
use Zend\ServiceManager\ServiceLocatorInterface;

return new class implements ServiceFactoryInterface {
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        return new RdsValueCollectionRepository(
            $serviceLocator->get(PersistenceManager::class),
            'default'
        );
    }
};
