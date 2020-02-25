<?php


namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\Configuration\Configuration as DoctrineConfiguration;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Configuration extends DoctrineConfiguration implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function getMigrationsToExecute(string $direction, string $to) : array
    {
        $migrations = parent::getMigrationsToExecute($direction, $to);
        foreach ($migrations as $migration) {
            $migration->getMigration()->setServiceLocator($this->getServiceLocator());
        }
        return $migrations;
    }
}