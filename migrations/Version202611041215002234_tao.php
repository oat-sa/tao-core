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
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\scripts\install\SetWProofreaderConfig;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Register WProofreader CKEditor client module on stacks that predated SetWProofreaderConfig install action.
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202611041215002234_tao extends AbstractMigration
{
    private const CLIENT_MODULE = 'tao/ckeditor/wproofreaderBootstrap';

    public function getDescription(): string
    {
        return 'Register WProofreader client configuration for existing TAO instances (BOSAN-375)';
    }

    public function up(Schema $schema): void
    {
        $this->runAction(new SetWProofreaderConfig());
    }

    public function down(Schema $schema): void
    {
        ClientLibConfigRegistry::getRegistry()->remove(self::CLIENT_MODULE);
        $this->addReport(Report::createSuccess('Removed WProofreader client configuration.'));
    }
}
