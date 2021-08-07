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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\update\OntologyUpdater;
use oat\generis\persistence\PersistenceManager;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use common_persistence_SqlPersistence as SqlPersistence;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;

final class Version202108050757012234_tao extends AbstractMigration
{
    /** @var SqlPersistence */
    private $persistence;

    public function getDescription(): string
    {
        return sprintf(
            'Synchronize models to add new Remote Lists field. Add new column to "%s" table.',
            RdsValueCollectionRepository::TABLE_LIST_ITEMS
        );
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        [$fromSchema, $schema] = $this->getSchemas();
        $this->addColumn($schema);
        $this->updateSchema($fromSchema, $schema);

        $this->addReport(
            Report::createSuccess(
                sprintf(
                    'Column "%s" successfully added to table "%s"',
                    RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI,
                    RdsValueCollectionRepository::TABLE_LIST_ITEMS
                )
            )
        );
    }

    public function down(Schema $schema): void
    {
        [$fromSchema, $schema] = $this->getSchemas();
        $this->removeColumn($schema);
        $this->updateSchema($fromSchema, $schema);

        $this->addReport(
            Report::createSuccess(
                sprintf(
                    'Column "%s" successfully removed from table "%s"',
                    RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI,
                    RdsValueCollectionRepository::TABLE_LIST_ITEMS
                )
            )
        );
    }

    private function getSchemas(): array
    {
        /** @var Schema $schema */
        $schema = $this->getPersistence()->getDriver()->getSchemaManager()->createSchema();
        $fromSchema = clone $schema;

        return [$fromSchema, $schema];
    }

    private function addColumn(Schema $schema): void
    {
        $listItemsTable = $schema->getTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        if (!$listItemsTable->hasColumn(RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI)) {
            $listItemsTable->addColumn(
                RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI,
                'string',
                ['length' => 255, 'notnull' => false]
            );

            $listItemsTable->addIndex([RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI]);
        }
    }

    private function removeColumn(Schema $schema): void
    {
        $listItemsTable = $schema->getTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);

        if ($listItemsTable->hasColumn(RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI)) {
            $listItemsTable->dropColumn(RdsValueCollectionRepository::FIELD_DEPENDENCY_ITEM_URI);
        }
    }

    private function updateSchema(Schema $fromSchema, Schema $schema): void
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
