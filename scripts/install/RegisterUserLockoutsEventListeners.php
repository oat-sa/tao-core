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

use common_report_Report as Report;
use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\event\LoginFailedEvent;
use oat\tao\model\event\LoginSucceedEvent;
use oat\tao\model\user\UserLocks;

/**
 * Class RegisterUserLockoutsEventListeners
 * @package oat\tao\scripts\install
 * @author Ivan Klimchuk <ivan.klimchuk@taotesting.com>
 */
class RegisterUserLockoutsEventListeners extends InstallAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);

        $eventManager->attach(LoginFailedEvent::class, [UserLocks::SERVICE_ID, 'catchFailedLogin']);
        $eventManager->attach(LoginSucceedEvent::class, [UserLocks::SERVICE_ID, 'catchSucceedLogin']);

        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        return new Report(Report::TYPE_SUCCESS, 'User lockouts event listeners are registered');
    }
}
