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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

use oat\oatbox\service\ServiceManager;

require_once dirname(__FILE__) . '/../includes/raw_start.php';

$parms = $argv;
array_shift($parms);

if (count($parms) !== 1) {
    echo 'Usage: ' . __FILE__ . ' EXTENSION_ID ' . PHP_EOL;
    die(1);
}

$extId = array_shift($parms);

try {
    $serviceManager = ServiceManager::getServiceManager();
    $extensionManager = $serviceManager->get(common_ext_ExtensionsManager::SERVICE_ID);

    $extension = $extensionManager->getExtensionById($extId);

    $uninstaller = new tao_install_ExtensionUninstaller($extension);
    $uninstaller->uninstall();

    $serviceManager->getContainerBuilder()
        ->forceBuild();

    $message = __('Uninstalled %s', $extId);
} catch (common_Exception $e) {
    $message = __('Uninstall of %s failed', $extId) . ':  ' . $e->getMessage();
}

echo PHP_EOL . $message . PHP_EOL;
