<?php

declare(strict_types=1);

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
 */

namespace oat\tao\scripts\install;

use common_persistence_Manager as PersistenceManager;
use Doctrine\DBAL\Schema\SchemaException;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\notification\implementation\NotificationServiceAggregator;
use oat\tao\model\notification\implementation\RdsNotification;
use oat\tao\model\notification\NotificationServiceInterface;

class InstallNotificationTable extends InstallAction
{
    public function __invoke($params): void
    {
        $persistence = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById(RdsNotification::DEFAULT_PERSISTENCE);
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        /** @var \Doctrine\DBAL\Schema\Schema */
        $fromSchema = clone $schema;

        try {
            $queueTable = $schema->createtable(RdsNotification::NOTIF_TABLE);

            $queueTable->addOption('engine', 'MyISAM');
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_ID, 'integer', ['notnull' => true, 'autoincrement' => true]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_RECIPIENT, 'string', ['notnull' => true, 'length' => 255]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_STATUS, 'integer', ['default' => 0, 'notnull' => false, 'length' => 255]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_TITLE, 'string', ['length' => 255]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_MESSAGE, 'text', ['default' => null]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_SENDER, 'string', ['default' => null, 'notnull' => false, 'length' => 255]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_SENDER_NANE, 'string', ['default' => null, 'notnull' => false, 'length' => 255]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_CREATION, 'datetime', ['notnull' => true]);
            $queueTable->addColumn(RdsNotification::NOTIF_FIELD_UPDATED, 'datetime', ['notnull' => true]);
            $queueTable->setPrimaryKey([RdsNotification::NOTIF_FIELD_ID]);

            $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);

            foreach ($queries as $query) {
                $persistence->exec($query);
            }
        } catch (SchemaException $e) {
            \common_Logger::i('Database Schema already up to date.');
        }

        $queue = new NotificationServiceAggregator();
        $queue->setServiceLocator($this->getServiceManager());
        $queue->setOption(
            'rds',
            [
                'class' => RdsNotification::class,
                'options' => [
                    RdsNotification::OPTION_PERSISTENCE => RdsNotification::DEFAULT_PERSISTENCE,
                    'visibility' => false,
                ],
            ]
        );

        $this->getServiceManager()->register(NotificationServiceInterface::SERVICE_ID, $queue);
    }
}
