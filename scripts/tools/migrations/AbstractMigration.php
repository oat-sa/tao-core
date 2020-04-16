<?php


namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\AbstractMigration as DoctrineAbstractMigration;
use oat\oatbox\service\ServiceManager;

abstract class AbstractMigration extends DoctrineAbstractMigration
{
    /**
     * Temporary helper until the service manager
     * gets properly injected migration scripts
     *
     * @return ServiceManager
     */
    public function getServiceManager(): ServiceManager
    {
        return ServiceManager::getServiceManager();
    }
}