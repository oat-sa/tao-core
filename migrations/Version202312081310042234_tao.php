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
 * Copyright (c) 2023(original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\generis\model\data\event\ClassPropertyDeletedEvent;
use oat\generis\model\data\event\ResourceDeleted;
use oat\oatbox\event\EventManager;
use oat\tao\model\listener\ClassPropertyRemovedListener;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202312081310042234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register property cache warmup and modify listeners.';
    }

    public function up(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach(
            ResourceDeleted::class,
            [ClassPropertyRemovedListener::SERVICE_ID, 'handleDeletedEvent']
        );
        $eventManager->attach(
            ClassPropertyDeletedEvent::class,
            [ClassPropertyRemovedListener::SERVICE_ID, 'handleDeletedEvent']
        );
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
