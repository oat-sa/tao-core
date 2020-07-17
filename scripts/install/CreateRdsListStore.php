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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use common_persistence_sql_Driver as SqlDriver;
use common_persistence_SqlPersistence as SqlPersistence;
use common_report_Report as Report;
use Doctrine\DBAL\Schema\Schema;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;

class CreateRdsListStore extends InstallAction
{
    public function __invoke($params = [])
    {
        $persistence = $this->getPersistence();

        /** @var SqlDriver $driver */
        $driver = $persistence->getDriver();

        /** @var Schema $schema */
        $schema     = $driver->getSchemaManager()->createSchema();
        $fromSchema = clone $schema;

        $this->create($schema);

        $queries = $persistence->getPlatForm()->getMigrateSchemaSql($fromSchema, $schema);

        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        return Report::createSuccess(
            sprintf('Table "%s" successfully created', RdsValueCollectionRepository::TABLE_LIST_ITEMS)
        );
    }

    public function create(Schema $schema): void
    {
        $listItemsTable = $schema->createTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_ID, 'integer', ['autoincrement' => true]);
        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_LABEL, 'string', ['length' => 255]);
        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_URI, 'string', ['length' => 255]);
        $listItemsTable->addColumn(RdsValueCollectionRepository::FIELD_ITEM_LIST_URI, 'string', ['length' => 255]);

        $listItemsTable->setPrimaryKey([RdsValueCollectionRepository::FIELD_ITEM_ID]);

        $listItemsTable->addIndex([RdsValueCollectionRepository::FIELD_ITEM_LABEL]);
        $listItemsTable->addIndex([RdsValueCollectionRepository::FIELD_ITEM_LIST_URI]);
        $listItemsTable->addUniqueIndex([RdsValueCollectionRepository::FIELD_ITEM_URI]);
    }

    private function getPersistence(): SqlPersistence
    {
        $persistenceManager = $this->serviceLocator->get(PersistenceManager::SERVICE_ID);

        return $persistenceManager->getPersistenceById('default');
    }
}
