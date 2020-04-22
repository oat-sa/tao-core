<?php

declare(strict_types=1);

namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\MigrationRepository;
use \Doctrine\Migrations\Metadata\AvailableMigration;
use oat\oatbox\service\ServiceManager;

/**
 * Class TaoMigrationRepository
 * @package oat\tao\scripts\tools\migrations
 */
class TaoMigrationRepository extends MigrationRepository
{
    /** @throws MigrationException */
    public function registerMigration(string $migrationClassName) : AvailableMigration
    {
        $migration = parent::registerMigration($migrationClassName);
        $migration->getMigration()->setServiceLocator(ServiceManager::getServiceManager());
        return $migration;
    }
}
