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

/**
 * Asset service to retrieve assets easily based on a config
 *
 * The service can be instantiated with the following options :
 *  - base : the base URL
 *  - buster : the cache buster value
 *  - no-buster : don't use a cache buster
 *
 * @author Antoine Robin
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class AssetService extends ConfigurableService
{
    const SERVICE_ID = 'tao/asset';

    //the query param key of the cache buster
    const CACHE_BUSTER_KEY = 'buster';

    /**
     * Get the full URL of an asset
     *
     * @param string $asset the asset path, relative, from the views folder
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

        if(!$this->hasOption('noBuster')) {
            $url .= '?' . self::CACHE_BUSTER_KEY . '=' . urlencode($this->getCacheBuster());
        }
        return $url;
    }

    public function getJsBaseWww($extensionId)
    {
        return $this->getAssetBaseUrl() . $extensionId . '/views/';
    }

    /**
     * @deprecated use getAssetBaseUrl
     */
    protected function getAssetUrl()
    {
        return $this->hasOption('base') ? $this->getOption('base') : ROOT_URL;
    }

    /**
     * Get the asset BASE URL
     * @return string the base URL
     */
    protected function getAssetBaseUrl()
    {
        $baseUrl = $this->hasOption('base') ? $this->getOption('base') : ROOT_URL;

        $baseUrl = trim($baseUrl);
        if(substr($baseUrl, -1) != '/'){
            $baseUrl .= '/';
        }

        return $baseUrl;
    }


    /**
     * Get a the cache buster value
     * @return string the busteri value
     */
    protected function getCacheBuster()
    {
        return $this->hasOption('buster') ? $this->getOption('buster') : TAO_VERSION;
    }
}
