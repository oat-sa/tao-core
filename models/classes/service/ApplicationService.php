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
    const SERVICE_ID = 'tao/applicationService';

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

        if(defined('ROOT_PATH') && is_readable(ROOT_PATH.'build')){
            $content = file_get_contents(ROOT_PATH.'build');
            $version = 'v' . $version;
            $version = is_numeric($content) ? $version. '+build' . $content : $version;
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