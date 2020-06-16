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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\listener\ClassPropertiesChangedListener;

class RegisterClassPropertiesChangedEventListener extends InstallAction
{
    /**
     * @param $params
     *
     * @return \common_report_Report
     * @throws \common_Exception
     * @throws InvalidServiceManagerException
     */
    public function __invoke($params): common_report_Report
    {
        $this->getServiceManager()->register(ClassPropertiesChangedListener::SERVICE_ID, new ClassPropertiesChangedListener());

        return new common_report_Report(common_report_Report::TYPE_SUCCESS, 'ClassPropertiesChangedListener is registered');
    }
}
