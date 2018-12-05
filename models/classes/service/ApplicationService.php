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

namespace oat\tao\model\service;

use common_ext_ExtensionsManager;
use common_exception_Error;
use oat\oatbox\service\ConfigurableService;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationService extends ConfigurableService
{
    const SERVICE_ID = 'tao/ApplicationService';

    const OPTION_BUILD_NUMBER = 'taoBuildNumber';

    /**
     * Returns a whenever or not the current instance is used as demo instance
     *
     * @return boolean
     */
    public function isDemo() {
        $releaseStatus = $this->getConstantValue('TAO_RELEASE_STATUS');

        return in_array($releaseStatus, array('demo', 'demoA', 'demoB', 'demoS'));
    }

    /**
     * @return string
     */
    public function getVersionName()
    {
        $version = $this->getPlatformVersion();

        if ($this->hasOption(self::OPTION_BUILD_NUMBER)) {
            $buildNumber = $this->getOption(self::OPTION_BUILD_NUMBER);
            $version = 'v' . $version;
            $version = is_numeric($buildNumber) ? "{$version}+build{$buildNumber}" : $version;
        }

        return $version;
    }

    /**
     * @return string
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    public function getProductName() {
        return $this->getConstantValue('PRODUCT_NAME');
    }

    /**
     * @return string
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    public function getPlatformVersion() {
        return $this->getConstantValue('TAO_VERSION');
    }

    /**
     * @return string
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    public function getDefaultEncoding() {
        return $this->getConstantValue('TAO_DEFAULT_ENCODING');
    }

    /**
     * Return true if platform is on debug mode
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return defined('DEBUG_MODE') && (DEBUG_MODE === true);
    }

    /**
     * @param string $constantName
     * @return string
     *
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    private function getConstantValue($constantName)
    {
        $serviceLocator = $this->getServiceLocator();
        if (!$serviceLocator instanceof ServiceLocatorInterface) {
            throw new common_exception_Error();
        }

        return $serviceLocator->get(common_ext_ExtensionsManager::SERVICE_ID)
            ->getExtensionById('tao')
            ->getConstant($constantName);
    }
}