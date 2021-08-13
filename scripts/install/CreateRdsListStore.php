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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 *
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use oat\oatbox\reporting\Report;
use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\extension\InstallAction;
use oat\generis\persistence\PersistenceManager;
use common_persistence_SqlPersistence as SqlPersistence;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;

class CreateRdsListStore extends InstallAction
{
    public function __invoke($params = [])
    {
        [$fromSchema, $schema] = $this->getSchemas();
        $this->create($schema);
        $this->createListItemsDependenciesTable($schema);
        $this->migrate($fromSchema, $schema);

        return Report::createSuccess(
            sprintf(
                'Tables "%s" and "%s" successfully created',
                RdsValueCollectionRepository::TABLE_LIST_ITEMS,
                RdsValueCollectionRepository::TABLE_LIST_ITEMS_DEPENDENCIES
            )
        );
    }

    public function create(Schema $schema): void
    {
        $listItemsTable = $schema->createTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        $listItemsTable->addColumn(
            RdsValueCollectionRepository::FIELD_ITEM_ID,
            'integer',
            ['autoincrement' => true]
        );
        $listItemsTable->addColumn(
            RdsValueCollectionRepository::FIELD_ITEM_LABEL,
            'string',
            ['length' => 255]
        );
        $listItemsTable->addColumn(
            RdsValueCollectionRepository::FIELD_ITEM_URI,
            'string',
            ['length' => 255]
        );
        $listItemsTable->addColumn(
            RdsValueCollectionRepository::FIELD_ITEM_LIST_URI,
            'string',
            ['length' => 255]
        );

        $listItemsTable->setPrimaryKey([RdsValueCollectionRepository::FIELD_ITEM_ID]);

        $listItemsTable->addIndex([RdsValueCollectionRepository::FIELD_ITEM_LABEL]);
        $listItemsTable->addIndex([RdsValueCollectionRepository::FIELD_ITEM_LIST_URI]);
        $listItemsTable->addUniqueIndex([RdsValueCollectionRepository::FIELD_ITEM_URI]);
    }

    private function getSchemas(): array
    {
        /** @var Schema $schema */
        $schema = $this->getPersistence()->getDriver()->getSchemaManager()->createSchema();
        $fromSchema = clone $schema;

        return [$fromSchema, $schema];
    }

    private function createListItemsDependenciesTable(Schema $schema): void
    {
        $listItemsDependenciesTable = $schema->createTable(
            RdsValueCollectionRepository::TABLE_LIST_ITEMS_DEPENDENCIES
        );

        $listItemsDependenciesTable->addColumn(
            RdsValueCollectionRepository::FIELD_LIST_ITEM_ID,
            'integer'
        );
        $listItemsDependenciesTable->addColumn(
            RdsValueCollectionRepository::FIELD_LIST_ITEM_FIELD,
            'string',
            ['length' => 255]
        );
        $listItemsDependenciesTable->addColumn(
            RdsValueCollectionRepository::FIELD_LIST_ITEM_VALUE,
            'string',
            ['length' => 255]
        );

        $listItemsDependenciesTable->addIndex([RdsValueCollectionRepository::FIELD_LIST_ITEM_ID]);
        $listItemsDependenciesTable->addIndex([RdsValueCollectionRepository::FIELD_LIST_ITEM_FIELD]);
        $listItemsDependenciesTable->addIndex([RdsValueCollectionRepository::FIELD_LIST_ITEM_VALUE]);

        $listItemsDependenciesTable->addForeignKeyConstraint(
            RdsValueCollectionRepository::TABLE_LIST_ITEMS,
            [RdsValueCollectionRepository::FIELD_LIST_ITEM_ID],
            [RdsValueCollectionRepository::FIELD_ITEM_ID]
        );
    }

    private function migrate(Schema $fromSchema, Schema $schema): void
    {
        $queries = $this->getPersistence()->getPlatForm()->getMigrateSchemaSql($fromSchema, $schema);

        foreach ($queries as $query) {
            $this->getPersistence()->exec($query);
        }
    }

    private function getPersistence(): SqlPersistence
    {
        if (!isset($this->persistence)) {
            $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);

            $this->persistence = $persistenceManager->getPersistenceById('default');
        }

        return $this->persistence;
    }
}
