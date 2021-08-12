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
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\generis\persistence\PersistenceManager;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use common_persistence_SqlPersistence as SqlPersistence;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;

final class Version202007151446482234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create an RDS store for lists';
    }

    public function up(Schema $schema): void
    {
        $fromSchema = clone $schema;
        $this->createTable($schema);
        $this->migrate($fromSchema, $schema);

        $this->addReport(
            Report::createSuccess(
                sprintf(
                    'Table "%s" successfully created',
                    RdsValueCollectionRepository::TABLE_LIST_ITEMS
                )
            )
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        $this->addReport(
            Report::createSuccess(
                sprintf(
                    'Table "%s" successfully dropped',
                    RdsValueCollectionRepository::TABLE_LIST_ITEMS
                )
            )
        );
    }

    private function createTable(Schema $schema): void
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
