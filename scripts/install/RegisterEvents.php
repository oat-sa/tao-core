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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types = 1);

namespace oat\tao\scripts\install;

use oat\oatbox\reporting\Report;
use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use common_ext_event_ExtensionInstalled;
use oat\tao\model\migrations\MigrationsService;
use oat\tao\model\ParamConverter\Event\ParamConverterEvent;
use oat\tao\model\ParamConverter\EventListener\ParamConverterListener;

class RegisterEvents extends InstallAction
{
    public function __invoke($params)
    {
        $eventManager = $this->getEventManager();
        $eventManager->attach(
            common_ext_event_ExtensionInstalled::class,
            [MigrationsService::SERVICE_ID, 'extensionInstalled']
        );
        $eventManager->attach(ParamConverterEvent::class, [ParamConverterListener::class, 'handleEvent']);
        $this->getServiceLocator()->register(EventManager::SERVICE_ID, $eventManager);

        return Report::createSuccess('Events registered');
    }

    private function getEventManager(): EventManager
    {
        return $this->getServiceLocator()->getContainer()->get(EventManager::SERVICE_ID);
    }
}
