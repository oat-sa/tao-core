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
use oat\oatbox\event\EventManager;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\model\ParamConverter\Event\ParamConverterEvent;
use oat\tao\model\ParamConverter\EventListener\ParamConverterListener;

final class Version202109300806032234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Attach ParamConverterEvent to EventManager.';
    }

    public function up(Schema $schema): void
    {
        $eventManager = $this->getEventManager();
        $eventManager->attach(ParamConverterEvent::class, [ParamConverterListener::class, 'handleEvent']);
        $this->getServiceLocator()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        $eventManager = $this->getEventManager();
        $eventManager->detach(ParamConverterEvent::class, [ParamConverterListener::class, 'handleEvent']);
        $this->getServiceLocator()->register(EventManager::SERVICE_ID, $eventManager);
    }

    private function getEventManager(): EventManager
    {
        return $this->getServiceLocator()->getContainer()->get(EventManager::SERVICE_ID);
    }
}
