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

use oat\oatbox\reporting\Report;
use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\update\OntologyUpdater;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202108170642002234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronize models to add new "Depends on property" class property';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        $this->addReport(Report::createSuccess('Models were successfully synchronized'));
    }

    public function down(Schema $schema): void
    {
        $this->addReport(Report::createWarning('Nothing to execute'));
    }
}
