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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202203260958222234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove media alignment status flag';
    }

    public function up(Schema $schema): void
    {
        $this->getClientLibConfigRegistry()->remove('ui/image/ImgStateActive');

        $this->addReport(Report::createSuccess('Image Alignment status flag removed'));
    }

    public function down(Schema $schema): void
    {
        $this->getClientLibConfigRegistry()->register(
            'ui/image/ImgStateActive',
            [
                'mediaAlignment' => false,
            ]
        );

        $this->addReport(Report::createSuccess('Image Alignment plugin disabled'));
    }

    private function getClientLibConfigRegistry(): ClientLibConfigRegistry
    {
        return $this->getServiceLocator()->get(ClientLibConfigRegistry::class);
    }
}
