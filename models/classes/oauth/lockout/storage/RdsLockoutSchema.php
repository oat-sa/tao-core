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
 * Copyright (c) 2020 (original work) (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT)
 */

namespace oat\tao\model\oauth\lockout\storage;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use oat\oatbox\service\ConfigurableService;

/**
 * Class RdsLockoutSchema
 * @package oat\tao\model\oauth\lockout\storage
 */
class RdsLockoutSchema extends ConfigurableService
{
    /**
     * @param Table $table
     */
    public function createLockoutsTable(Table $table)
    {
        $table->addColumn(RdsLockoutStorage::FIELD_ID, 'bigint', ['notnull' => false]);
        $table->addColumn(RdsLockoutStorage::FIELD_ADDRESS, 'string', ['notnull' => false, 'length' => '15']); // pattern 000.000.000.000
        $table->addColumn(RdsLockoutStorage::FIELD_ATTEMPTS, 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn(RdsLockoutStorage::FIELD_EXPIRE_AT, 'integer', ['notnull' => false]);

        $table->setPrimaryKey([RdsLockoutStorage::FIELD_ID]);
    }

    /**
     * @param Schema $schema
     *
     * @return Schema
     */
    public function getSchema(Schema $schema): Schema
    {
        $table = $schema->createTable(RdsLockoutStorage::TABLE_NAME);
        $this->createLockoutsTable($table);

        return $schema;
    }
}
