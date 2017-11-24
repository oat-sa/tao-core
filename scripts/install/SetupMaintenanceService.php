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

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\maintenance\Maintenance;

class SetupMaintenanceService extends InstallAction
{
    public function __invoke($params)
    {
        if ($this->getServiceManager()->has(Maintenance::SERVICE_ID)) {
            return \common_report_Report::createSuccess(__('Maintenance service is already installed.'));
        }

        $maintenancePersistence = 'maintenance';

        try {
            \common_persistence_Manager::getPersistence($maintenancePersistence);
        } catch (\common_Exception $e) {
            \common_persistence_Manager::addPersistence($maintenancePersistence,  array('driver' => 'phpfile'));
        }

        $service = new Maintenance();
        $service->setOption(Maintenance::OPTION_PERSISTENCE, $maintenancePersistence);
        $this->getServiceManager()->register(Maintenance::SERVICE_ID, $service);

        if (defined('SYS_READY')) {
            if (SYS_READY === false) {
                $this->getServiceManager()->get(Maintenance::SERVICE_ID)->disablePlatform();
                return \common_report_Report::createSuccess(__('Maintenance service was installed. Platform is disabled.'));
            }
        }

        $this->getServiceManager()->get(Maintenance::SERVICE_ID)->enablePlatform();
        return \common_report_Report::createSuccess(__('Maintenance service was installed. Platform is enabled.'));
    }

}