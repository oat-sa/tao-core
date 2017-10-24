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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\actionQueue\tools;

use oat\oatbox\extension\AbstractAction;
use oat\tao\model\actionQueue\ActionQueue;
use \common_report_Report as Report;

/**
 * Class ActionClearAbandonedPositions
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ActionClearAbandonedPositions extends AbstractAction
{

    public function __invoke($params)
    {
        $report = new Report(Report::TYPE_INFO, __('Collecting of abandoned actions in the action queue ...'));
        /** @var ActionQueue $actionQueueService */
        $actionQueueService = $this->getServiceManager()->get(ActionQueue::SERVICE_ID);
        $actions = $actionQueueService->getOption(ActionQueue::OPTION_ACTIONS);
        foreach ($actions as $actionClass => $actionConfig) {
            $action = new $actionClass();
            $removed = $actionQueueService->clearAbandonedPositions($action);
            $report->add(new Report(Report::TYPE_SUCCESS, __('Action %s - removed %s positions', $action->getId(), $removed)));
        }
        return $report;
    }
}