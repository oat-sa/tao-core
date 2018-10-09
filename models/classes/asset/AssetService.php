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
 * Copyright (c) 2014-2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\asset;

use oat\oatbox\service\ConfigurableService;
use Jig\Utils\FsUtils;
use oat\tao\model\service\ApplicationService;

/**
 * Asset service to retrieve assets easily based on a config
 *
 * The service can be instantiated with the following options :
 *  - base : the base URL
 *  - buster : the cache buster value (false means no buster)
 *
 * @author Antoine Robin
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class AssetService extends ConfigurableService
{
    const SERVICE_ID = 'tao/asset';

    //the query param key of the cache buster
    const BUSTER_QUERY_KEY = 'buster';

    //key to get the base
    const BASE_OPTION_KEY  = 'base';

    //key to get the buster value
    const BUSTER_OPTION_KEY = 'buster';

    /**
     * Get the full URL of an asset or a folder
     *
     * @param string $asset the asset or folder path, relative, from the views folder
     * @param string $extensionId  if the asset is relative to an extension base www (optional)
     * @return string the asset URL
     */
    public function getAsset($asset, $extensionId = null)
    {
        if( ! is_null($extensionId)){
            $url = $this->getJsBaseWww($extensionId) . FsUtils::normalizePath($asset);
        } else {
            $url = $this->getAssetBaseUrl() . FsUtils::normalizePath($asset);
        }

        $isFolder = (substr_compare($url, '/', strlen($url) - 1) === 0);

        $buster = $this->getCacheBuster();
        if($buster != false && $isFolder == false) {
            $url .= '?' . self::BUSTER_QUERY_KEY . '=' . urlencode($buster);
        }

        return $url;
    }

    /**
     * Get the asset base of a given extension (should be getBaseWww)
     * @param string $extensionId
     * @return string the base URL
     */
    public function getJsBaseWww($extensionId)
    {
        return $this->getAssetBaseUrl() . $extensionId . '/views/';
    }

    /**
     * @deprecated use getAssetBaseUrl
     */
    protected function getAssetUrl()
    {
        return $this->hasOption(self::BASE_OPTION_KEY) ? $this->getOption(self::BASE_OPTION_KEY) : ROOT_URL;
    }

    /**
     * Get the asset BASE URL
     * @return string the base URL
     */
    protected function getAssetBaseUrl()
    {
        $baseUrl = $this->hasOption(self::BASE_OPTION_KEY) ? $this->getOption(self::BASE_OPTION_KEY) : ROOT_URL;

        $baseUrl = trim($baseUrl);
        if(substr($baseUrl, -1) != '/'){
            $baseUrl .= '/';
        }

        return $baseUrl;
    }

    /**
     * Get a the cache buster value, if none we use the tao version.
     * @return string the busteri value
     */
    public function getCacheBuster()
    {
        if ($this->hasOption(self::BUSTER_OPTION_KEY)) {
            return $this->getOption(self::BUSTER_OPTION_KEY);
        } else {
            return $this->getServiceLocator()->get(ApplicationService::SERVICE_ID)->getPlatformVersion();
        }
    }

    /**
     * Change the cache buster value
     * @param string|bool $buster the new buster value, false means no buster at all
     */
    public function setCacheBuster($buster)
    {
        return $this->setOption(self::BUSTER_OPTION_KEY, $buster);
    }
}
