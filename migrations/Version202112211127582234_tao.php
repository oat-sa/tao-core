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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\model\Lists\DataAccess\Repository\RdsValueCollectionRepository;

final class Version202112211127582234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change list_items.label length from 255 characters to 512.';
    }

    public function up(Schema $schema): void
    {
        $listItemsTable = $schema->getTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);
        $listItemsTable->changeColumn(
            RdsValueCollectionRepository::FIELD_ITEM_LABEL,
            ['length' => 512]
        );

        $this->addReport(Report::createSuccess('list_items.label length changed from 255 to 512 characters.'));
    }

    public function down(Schema $schema): void
    {
        $listItemsTable = $schema->getTable(RdsValueCollectionRepository::TABLE_LIST_ITEMS);
        $listItemsTable->changeColumn(
            RdsValueCollectionRepository::FIELD_ITEM_LABEL,
            ['length' => 255]
        );

        $this->addReport(Report::createSuccess('list_items.label length changed from 512 to 255 characters.'));
    }
}
