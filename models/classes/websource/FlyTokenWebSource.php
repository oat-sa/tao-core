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
namespace oat\tao\model\websource;

use common_ext_ExtensionsManager;

/**
 * @access public
 * @package tao
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class FlyTokenWebSource extends TokenWebSource
{
    const ENTRY_POINT = '/getFileFlysystem.php/';

    static $instances = [];

    /**
     * /**
     * get instance from url.
     * @param string|null $url
     * @return FlyTokenWebSource
     * @throws \common_exception_InconsistentData
     * @throws WebsourceNotFound
     * @throws \tao_models_classes_FileNotFoundException
     */
    public static function createFromUrl($url = null)
    {
        if ($url === null) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $rel = substr($url, strpos($url, self::ENTRY_POINT) + strlen(self::ENTRY_POINT));
        $parts = explode('/', $rel, 2);
        list ($webSourceId) = $parts;
        $webSourceId = preg_replace('/[^a-zA-Z0-9]*/', '', $webSourceId);
        if (!isset($instances[$webSourceId])) {
            $configPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'tao' . DIRECTORY_SEPARATOR . 'websource_' . $webSourceId . '.conf.php';

            if (!file_exists($configPath)) {
                throw new \tao_models_classes_FileNotFoundException("Config file not found");
            }

            $config = include $configPath;
            if (!is_array($config) || !isset($config['className'])) {
                throw new WebsourceNotFound('Undefined websource ' . $webSourceId);
            }
            $className = $config['className'];
            $options = isset($config['options']) ? $config['options'] : array();
            $instances[$webSourceId] = new $className($options);
            if (!$instances[$webSourceId] instanceof TokenWebSource) {
                throw new \common_exception_InconsistentData('Unexpected websource class');
            }
        }
        return $instances[$webSourceId];
    }

    /**
     * Get file path from url.
     * @param null $url
     * @return string
     * @throws \tao_models_classes_FileNotFoundException
     */
    public function getFilePathFromUrl($url = null)
    {
        if ($url === null) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $url = parse_url($url)['path']; //remove query part from url.
        $rel = substr($url, strpos($url, self::ENTRY_POINT) + strlen(self::ENTRY_POINT));
        $parts = explode('/', $rel, 4);
        list ($webSourceId, $timestamp, $token, $subPath) = $parts;

        $parts = explode('*/', $subPath, 2);
        if (count($parts) < 2) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        list ($subPath, $file) = $parts;

        $secret = $this->getOption('secret');
        $ttl = $this->getOption('ttl');

        $correctToken = md5($timestamp . $subPath . $secret);

        if (time() - $timestamp > $ttl || $token != $correctToken) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }

        $path = array();
        foreach (explode('/', $subPath . $file) as $ele) {
            $path[] = rawurldecode($ele);
        }
        $filename = implode(DIRECTORY_SEPARATOR, $path);

        return $filename;
    }

    /**
     * @param string $relativePath
     * @return string
     * @throws \common_exception_Error
     * @throws \common_ext_ExtensionException
     */
    public function getAccessUrl($relativePath) {
        $path = array();
        foreach (explode(DIRECTORY_SEPARATOR, ltrim($relativePath, DIRECTORY_SEPARATOR)) as $ele) {
            $path[] = rawurlencode($ele);
        }
        $relUrl = implode('/', $path);
        $token = $this->generateToken($relUrl);
        $taoExtension = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        return $taoExtension->getConstant('BASE_URL').'getFileFlysystem.php/'.$this->getId().'/'.$token.'/'.$relUrl.'*/';
    }
}