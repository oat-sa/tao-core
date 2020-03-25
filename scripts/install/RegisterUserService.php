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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\user\UserService;
use oat\tao\model\user\GenerisUserService;

class RegisterUserService extends InstallAction
{
    public function __invoke($params)
    {
        if (!$this->getServiceManager()->has(UserService::SERVICE_ID)) {
            $this->getServiceManager()->register(UserService::SERVICE_ID, new GenerisUserService());
        }
        return \common_report_Report::createSuccess('User service successfully registered.');
    }
}
