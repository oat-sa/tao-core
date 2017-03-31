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
 * Copyright (c) 2017 Open Assessment Technologies SA
 *
 */

namespace oat\tao\scripts\tools\maintenance;

use oat\oatbox\action\Action;
use oat\tao\model\maintenance\Maintenance;
use oat\tao\model\maintenance\MaintenanceState;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Status implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Get the status of platform
     *
     * @param $params
     * @return \common_report_Report
     */
    public function __invoke($params)
    {
        try {
            $state = $this->getMaintenanceService()->getPlatformState();
            if ($this->getMaintenanceService()->isPlatformReady($state)) {
                return \common_report_Report::createSuccess(
                    __('TAO platform is live since %s.', $state->getDuration()->format(MaintenanceState::DATEDIFF_FORMAT))
                );
            } else {
                return \common_report_Report::createSuccess(
                    __('TAO platform is under maintenance since %s', $state->getDuration()->format(MaintenanceState::DATEDIFF_FORMAT))
                );
            }
        } catch (\common_Exception $e) {
            return \common_report_Report::createFailure(__('Error: %s', $e->getMessage()));
        }
    }

    /**
     * Get the maintenance service
     *
     * @return Maintenance array|object
     */
    protected function getMaintenanceService()
    {
        return $this->getServiceLocator()->get(Maintenance::SERVICE_ID);
    }

}