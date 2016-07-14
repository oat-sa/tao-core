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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\layout;

/**
 * Let's you create an AMD script tag.
 */
class AmdLoader {

    /**
     * Will be the tag id
     */
    const LOADER_ID = 'amd-loader';

    /**
     * The configuration URL (data-config)
     */
    private $configUrl;

    /**
     * The URL to the require.js lib
     */
    private $requireJsUrl;

    /**
     * The bootstrap module URL
     */
    private $bootstrapUrl;

    /**
     * Create an instance using the configuration data
     * @param string $configUrl the configration URL
     * @param string $requireJsUrl the URL of the require.js lib
     * @param string $bootstrapUrl the bootstrap module URL
     */
    public function __construct($configUrl, $requireJsUrl, $bootstrapUrl){
        $this->configUrl = $configUrl;
        $this->requireJsUrl = $requireJsUrl;
        $this->bootstrapUrl = $bootstrapUrl;
    }

    /**
     * Get the loader for the bundle mode
     * @param string $bundle the URL of the bundle
     * @param string $controller the controller module id
     * @param array  $params additionnal parameters to give to the loader
     *
     * @return the generated script tag
     */
    public function getBundleLoader($bundle, $controller, $params = null){
        $attributes = [
            'data-config'     => $this->configUrl,
            'src'             => $bundle,
            'data-controller' => $controller
        ];

        if(!is_null($params)){
            $attributes['data-params'] = json_encode($params);
        }

        return $this->buildScriptTag($attributes);
    }

    /**
     * Get the loader for the dynamic mode
     * @param string $controller the controller module id
     * @param array  $params additionnal parameters to give to the loader
     *
     * @return the generated script tag
     */
    public function getDynamicLoader($controller, $params = null){
        $attributes = [
            'data-config'     => $this->configUrl,
            'src'             => $this->requireJsUrl,
            'data-main'       => $this->bootstrapUrl,
            'data-controller' => $controller
        ];

        if(!is_null($params)){
            $attributes['data-params'] = json_encode($params);
        }

        return $this->buildScriptTag($attributes);
    }

    /**
     * Build the script tag
     * @param array  $attributes key/val to create tag's attributes
     *
     * @return the generated script tag
     */
    private function buildScriptTag($attributes){
        $amdScript = '<script id="' . self::LOADER_ID . '" ';
        foreach($attributes as $attr => $value) {
            $amdScript .= $attr . '="' . \tao_helpers_Display::htmlize($value) . '" ';
        }
        return trim($amdScript) . '></script>';
    }
}
