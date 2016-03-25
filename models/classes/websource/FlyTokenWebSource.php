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

use core_kernel_fileSystem_FileSystem;
use common_ext_ExtensionsManager;

/**
 * @access public
 * @package tao
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class FlyTokenWebSource extends TokenWebSource
{
    const ENTRY_POINT = '/getFileFlysystem.php/';

    public static function getFilePath()
    {
        $rel = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], self::ENTRY_POINT) + strlen(self::ENTRY_POINT));
        $parts = explode('/', $rel, 4);
        list ($webSourceId, $timestamp, $token, $subPath) = $parts;
        $webSourceId = preg_replace('/[^a-zA-Z0-9]*/', '', $webSourceId);
        $configPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'tao' . DIRECTORY_SEPARATOR . 'websource_' . $webSourceId . '.conf.php';

        if (!file_exists($configPath)) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }

        $parts = explode('*/', $subPath, 2);
        if (count($parts) < 2) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        list ($subPath, $file) = $parts;

        $config = include $configPath;
        $compiledPath = $config['options']['path'];
        $secretPassphrase = $config['options']['secret'];
        $ttl = $config['options']['ttl'];

        $correctToken = md5($timestamp . $subPath . $secretPassphrase);

        if (time() - $timestamp > $ttl || $token != $correctToken) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }

        $path = array();
        foreach (explode('/', $subPath . $file) as $ele) {
            $path[] = rawurldecode($ele);
        }
        $filename = $compiledPath . implode(DIRECTORY_SEPARATOR, $path);
        if (strpos($filename, '?')) {
            // A query string is provided with the file to be retrieved - clean up!
            $parts = explode('?', $filename);
            $filename = $parts[0];
        }

        return $filename;
    }

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

    /**
     * @param $filePath
     * @throws \tao_models_classes_FileNotFoundException
     * @return Stream
     */
    public function getFileStream($filePath)
    {
        if ($filePath === '') {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        $fs = $this->getFileSystem();
        try {
            $resource = $fs->readStream($filePath);
        } catch(FileNotFoundException $e) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        return new Stream($resource);
    }
}