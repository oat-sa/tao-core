<?php

declare(strict_types=1);

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
 *
 */

namespace oat\tao\scripts\install;

use common_cache_Cache;
use common_Exception;
use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\resources\GetAllChildrenCacheKeyFactory;
use oat\tao\model\resources\SecureResourceCachedService;
use oat\tao\model\resources\SecureResourceService;
use oat\tao\model\resources\SecureResourceServiceInterface;
use oat\tao\model\resources\ValidatePermissionsCacheKeyFactory;

class InstallSecureResourceCachedService extends InstallAction
{
    /**
     * @param $params
     *
     * @return common_report_Report
     *
     * @throws common_Exception
     */
    public function __invoke($params)
    {
        $this->registerService(
            SecureResourceServiceInterface::SERVICE_ID,
            new SecureResourceCachedService(
                new SecureResourceService(),
                new ValidatePermissionsCacheKeyFactory(),
                new GetAllChildrenCacheKeyFactory(),
                common_cache_Cache::SERVICE_ID,
                null
            ),
            true
        );

        return common_report_Report::createSuccess('SecureResourceCachedService registered successfully');
    }
}
