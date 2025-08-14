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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA;
 *
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\reporting\Report;
use Doctrine\DBAL\Schema\Schema;
use oat\generis\persistence\PersistenceManager;
use common_persistence_SqlPersistence as SqlPersistence;

class RegisterUniqueIdFeature extends InstallAction
{
    private SqlPersistence $persistence;

    public function __invoke($params = []): Report
    {
        [$fromSchema, $schema] = $this->getSchemas();
        $this->createUniqueIdsTable($schema);
        $this->migrate($fromSchema, $schema);

        return Report::createSuccess('Unique IDs table successfully created');
    }

    private function createUniqueIdsTable(Schema $schema): void
    {
        $table = $schema->createTable('unique_ids');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('resource_id', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('resource_type', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('unique_id', 'string', ['length' => 9, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['resource_id'], 'idx_resource_id');
        $table->addUniqueIndex(['unique_id', 'resource_type'], 'uniq_unique_id_resource_type');
        $table->addIndex(['resource_type'], 'idx_unique_ids_resource_type');
    }

    private function getSchemas(): array
    {
        $schema = $this->getPersistence()->getDriver()->getSchemaManager()->createSchema();
        $fromSchema = clone $schema;

        return [$fromSchema, $schema];
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
