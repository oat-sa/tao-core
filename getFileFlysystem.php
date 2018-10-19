<?php
/**
 *
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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

require_once __DIR__ . '/../vendor/autoload.php';

use oat\tao\model\websource\FlyTokenWebSource;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\mvc\Bootstrap;

$url = $_SERVER['REQUEST_URI'];
$rel = substr($url, strpos($url, FlyTokenWebSource::ENTRY_POINT) + strlen(FlyTokenWebSource::ENTRY_POINT));
$parts = explode('/', $rel, 2);
list ($webSourceId) = $parts;
$webSourceId = preg_replace('/[^a-zA-Z0-9]*/', '', $webSourceId);

$root = dirname(__DIR__);
$bootstrap = new Bootstrap($root .DIRECTORY_SEPARATOR. 'config' .DIRECTORY_SEPARATOR . 'generis.conf.php');

$serviceManager = $bootstrap->getServiceLocator();
common_Logger::singleton()->register();

/** @var common_ext_ExtensionsManager $e */
$e =  $serviceManager->get(common_ext_ExtensionsManager::SERVICE_ID);
$config = $e->getExtensionById('tao')->getConfig('websource_' . $webSourceId);
//$config = include $configPath;
if (!is_array($config) || !isset($config['className'])) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

$className = $config['className'];
$options = isset($config['options']) ? $config['options'] : array();
$source = new $className($options);
if (!$source instanceof FlyTokenWebSource) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

$fsService = $serviceManager->get(FileSystemService::SERVICE_ID);
$fileSystem = $fsService->getFileSystem($source->getOption($source::OPTION_FILESYSTEM_ID));
$source->setFileSystem($fileSystem);

try {
    $ttl = isset($options['ttl']) && $options['ttl'] ? $options['ttl'] : (30 * 60); //30 min default
    $path = $source->getFilePathFromUrl($url);
    $stream = $source->getFileStream($path);
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + $ttl));
    tao_helpers_Http::returnStream($stream, $source->getMimetype($path));
    $stream->detach();
} catch (\tao_models_classes_FileNotFoundException $e) {
    header("HTTP/1.0 404 Not Found");
}
exit();