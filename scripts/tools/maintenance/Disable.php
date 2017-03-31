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
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Disable implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function __invoke($params)
    {
        try {
            if ($this->getMaintenanceService()->isApplicationDisabled()) {
                return \common_report_Report::createSuccess(__('TAO platform is already on maintenance mode.'));
            }
            $this->getMaintenanceService()->setApplicationDisabled();
            return \common_report_Report::createSuccess(__('TAO platform is now under maintenance.'));
        } catch (\common_Exception $e) {
            return \common_report_Report::createFailure(__('Error: %s', $e->getMessage()));
        }
    }

    protected function getMaintenanceService()
    {
        return $this->getServiceLocator()->get(Maintenance::SERVICE_ID);
    }
}