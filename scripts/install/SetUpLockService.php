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
 * Copyright (c) 2014-2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\mutex\LockService;
use oat\oatbox\service\ServiceNotFoundException;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * This post-installation script configure lockService to use redis as store.
 */
class SetUpLockService extends InstallAction
{
    public function __invoke($params)
    {
        try {
            $this->getServiceManager()->get(LockService::SERVICE_ID);
        } catch (ServiceNotFoundException $e) {
            $service = new LockService([
                LockService::OPTION_PERSISTENCE_CLASS => RedisStore::class,
                LockService::OPTION_PERSISTENCE_OPTIONS => 'redis',
            ]);
            $this->getServiceManager()->register(LockService::SERVICE_ID, $service);
        }

        return \common_report_Report::createSuccess('LockService successfully configured.');
    }
}
