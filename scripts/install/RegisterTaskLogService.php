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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\taskQueue\TaskLog;
use oat\tao\model\taskQueue\TaskLog\Broker\RdsTaskLogBroker;
use oat\tao\model\taskQueue\TaskLogInterface;

/**
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class RegisterTaskLogService extends InstallAction
{
    public function __invoke($params)
    {
        $taskLogService = new TaskLog([
            TaskLogInterface::OPTION_TASK_LOG_BROKER => new RdsTaskLogBroker('default')
        ]);
        $this->registerService(TaskLogInterface::SERVICE_ID, $taskLogService);

        try {
            $taskLogService->createContainer();
        } catch (\Exception $e) {
            return \common_report_Report::createFailure('Creating task log container failed');
        }

        return \common_report_Report::createSuccess('Task log service successfully registered.');
    }
}