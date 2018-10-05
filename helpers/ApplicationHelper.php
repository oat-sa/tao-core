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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\helpers;

use oat\oatbox\service\ServiceManager;
use oat\tao\model\service\ApplicationService;

/**
 * Class ApplicationHelper
 * @package oat\tao\helpers
 *
 * @deprecated Use oat\tao\model\service\ApplicationService instead
 */
class ApplicationHelper
{
    /**
     * Returns a whenever or not the current instance is used as demo instance
     *
     * @return boolean
     */
    public static function isDemo() {
        return ServiceManager::getServiceManager()->get(ApplicationService::SERVICE_ID)->isDemo();
    }

    /**
     * @return string
     */
    public static function getVersionName()
    {
        return ServiceManager::getServiceManager()->get(ApplicationService::SERVICE_ID)->getVersionName();
    }

    /**
     * @return string
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    public function getProductName() {
        return ServiceManager::getServiceManager()->get(ApplicationService::SERVICE_ID)->getProductName();
    }

    /**
     * @return string
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    public function getPlatformVersion() {
        return ServiceManager::getServiceManager()->get(ApplicationService::SERVICE_ID)->getPlatformVersion();
    }
}