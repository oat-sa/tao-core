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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\install;

use Doctrine\DBAL\Schema\SchemaException;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\notification\implementation\AbstractRdsNotificationService;

class InstallNotificationTable extends InstallAction
{
    public function __invoke($params)
    {
        /** @var AbstractRdsNotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get(NotificationServiceInterface::SERVICE_ID);
        $persistence = $notificationService->getPersistence();
        
        //
        if ($persistence === null) {
            \common_Logger::i('No sql-based persistence configured. No database schema to install.');
            return;
        }

        /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $schemaManager */
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        
        $fromSchema = clone $schema;

        try {
            $notificationService->createNotificationTable($schema);

            $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);

            foreach ($queries as $query) {
                $persistence->exec($query);
            }

        } catch(SchemaException $e) {
            \common_Logger::i('Database Schema already up to date.');
        }
    }
}
