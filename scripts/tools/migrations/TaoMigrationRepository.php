<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\tools\migrations;

use Doctrine\Migrations\MigrationRepository;
use \Doctrine\Migrations\Metadata\AvailableMigration;
use oat\oatbox\service\ServiceManager;

/**
 * @inheritDoc
 * Class extends doctrine migration repository in order to add Tao specific features (such as injecting service manager
 * into the migration).
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
