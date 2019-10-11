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
use oat\tao\model\notification\implementation\NotificationServiceAggregator;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\tao\model\notification\implementation\AbstractRdsNotification;
use common_persistence_Manager as PersistenceManager;

class InstallNotificationTable extends InstallAction
{

    public function __invoke($params)
    {
        $persistence = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById(AbstractRdsNotification::DEFAULT_PERSISTENCE);
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        /**
         * @var \Doctrine\DBAL\Schema\Schema $fromSchema
         */
        $fromSchema = clone $schema;

        try {

            $queueTable = $schema->createtable(AbstractRdsNotification::NOTIF_TABLE);

            $queueTable->addOption('engine', 'MyISAM');
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_ID           , 'string'   , ['length' => 23, 'notnull' => true,]);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_RECIPIENT    , 'string'   , ['length' => 255, 'notnull' => true ]);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_STATUS       , 'integer'  , ['length' => 255]);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_TITLE        , 'string'   , ['length' => 255]);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_MESSAGE      , 'text'     , []);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_SENDER       , 'string'   , ['length' => 255]);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_SENDER_NANE  , 'string'   , ['length' => 255]);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_CREATION     , 'datetime' , ['notnull' => true]);
            $queueTable->addColumn(AbstractRdsNotification::NOTIF_FIELD_UPDATED      , 'datetime' , ['notnull' => true]);
            $queueTable->setPrimaryKey(array(AbstractRdsNotification::NOTIF_FIELD_ID));

            $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);

            foreach ($queries as $query) {
                $persistence->exec($query);
            }

        } catch(SchemaException $e) {
            \common_Logger::i('Database Schema already up to date.');
        }

        $queue = new NotificationServiceAggregator();
        $queue->setServiceLocator($this->getServiceManager());
        $queue->setOption('rds' ,
            array(
                'class'   => AbstractRdsNotification::class,
                'options' => [
                    AbstractRdsNotification::OPTION_PERSISTENCE => AbstractRdsNotification::DEFAULT_PERSISTENCE,
                    'visibility'  => false,
                ],
            )
        );

        $this->getServiceManager()->register(NotificationServiceInterface::SERVICE_ID, $queue);

    }

}
