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
use oat\oatbox\service\ServiceManager;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\SimpleConfigDriver;

$url = $_SERVER['REQUEST_URI'];
$rel = substr($url, strpos($url, FlyTokenWebSource::ENTRY_POINT) + strlen(FlyTokenWebSource::ENTRY_POINT));
$parts = explode('/', $rel, 2);
list ($webSourceId) = $parts;
$webSourceId = preg_replace('/[^a-zA-Z0-9]*/', '', $webSourceId);

$root = dirname(__DIR__);
$driver = new SimpleConfigDriver();
$configService = $driver->connect('config', array(
    'dir' => $root .DIRECTORY_SEPARATOR. 'config' .DIRECTORY_SEPARATOR,
    'humanReadable' => true
));
$serviceManager = new ServiceManager($configService);

$configPath = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'tao' . DIRECTORY_SEPARATOR . 'websource_' . $webSourceId . '.conf.php';

if (!file_exists($configPath)) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

$config = include $configPath;
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
    $path = $source->getFilePathFromUrl($url);
    $stream = $source->getFileStream($path);
    tao_helpers_Http::returnStream($stream, $source->getMimetype($path));
    $stream->detach();
} catch (\tao_models_classes_FileNotFoundException $e) {
    header("HTTP/1.0 404 Not Found");
}
exit();