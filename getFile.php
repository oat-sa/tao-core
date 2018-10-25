<?php
use oat\tao\model\websource\WebsourceManager;
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

require_once '../vendor/autoload.php';

$rel = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '/getFile.php/') + strlen('/getFile.php/'));
$parts = explode('/', $rel, 4);
if (count($parts) < 3) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

list ($ap, $timestamp, $token, $subPath) = $parts;
$parts = explode('*/', $subPath, 2);
// TODO add security check on url
if (count($parts) < 2) {
    header('HTTP/1.0 403 Forbidden');
    die();
}
list ($subPath, $file) = $parts;

$bootStrap = new oat\tao\model\mvc\Bootstrap('../config/generis.conf.php');
$config = common_ext_ExtensionsManager::singleton()->getExtensionById('tao')->getConfig('websource_' . $ap);
common_Logger::singleton()->register();

$compiledPath = $config['options']['path'];
$secretPassphrase = $config['options']['secret'];
$ttl = $config['options']['ttl'];

$correctToken = md5($timestamp . $subPath . $secretPassphrase);

if (time() - $timestamp > $ttl || $token != $correctToken) {
    header('HTTP/1.0 403 Forbidden');
    die();
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
$cacheTtl = $ttl ? $ttl : (30 * 60); //30 min default
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', $timestamp + $cacheTtl));

tao_helpers_Http::returnFile($filename);

exit();