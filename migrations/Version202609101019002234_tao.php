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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Reserved migration version for Crowdin en-US RDF pollution (BOSAN-360).
 *
 * Data cleanup ran on affected deployments when this version was first shipped.
 * Locale tagging is fixed in POFileReader/RdfPack; this migration is intentionally a no-op.
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202609101019002234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'No-op: en-US RDF cleanup already applied where needed; root cause fixed in translation sync';
    }

    public function up(Schema $schema): void
    {
        $this->addReport(Report::createSuccess('No-op: en-US RDF cleanup already applied where needed; root cause fixed in translation sync'));
    }

    public function down(Schema $schema): void
    {
        $this->addReport(Report::createSuccess('No-op: en-US RDF cleanup already applied where needed; root cause fixed in translation sync'));
    }
}
