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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\clientDetector;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\clientDetector\detector\DetectorInterface;

/**
 * Class SystemDetector
 *
 * A service to initialize web browser/OS detectors from configuration
 *
 * @package oat\tao\model\clientDetector
 */
class ClientDetectorService extends ConfigurableService
{
    const SERVICE_ID = 'tao/clientDetector';

    const OPTION_WEB_BROWSER_DETECTOR = 'web-browser-detector';
    const OPTION_OS_DETECTOR = 'os-detector';

    /**
     * Get the WebBrowserDetector service
     *
     * @return DetectorInterface
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function getWebBrowserDetector()
    {
        return $this->getSubService(self::OPTION_WEB_BROWSER_DETECTOR, DetectorInterface::class);
    }

    /**
     * Get the WebBrowserDetector service
     *
     * @return DetectorInterface
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function getOSDetector()
    {
        return $this->getSubService(self::OPTION_OS_DETECTOR, DetectorInterface::class);
    }

}