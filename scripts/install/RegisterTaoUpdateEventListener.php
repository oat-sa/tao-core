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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\scripts\install;

use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\event\TaoUpdateEvent;
use oat\tao\model\notifications\AlarmNotificationService;

/**
 * Class RegisterTaoUpdateEventListener
 * @package oat\tao\scripts\install
 * @author Ivan Klimchuk <andrei.niahrou@taotesting.com>
 */
class RegisterTaoUpdateEventListener extends InstallAction
{
    public function __invoke($params)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(TaoUpdateEvent::class, [AlarmNotificationService::SERVICE_ID, 'listenTaoUpdateEvent']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        return new Report(Report::TYPE_SUCCESS, 'Tao update listener are registered');
    }
}
