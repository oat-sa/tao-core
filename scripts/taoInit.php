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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

require_once dirname(__FILE__) . '/../includes/raw_start.php';

use oat\generis\scripts\tools;
use oat\oatbox\cache\SetupFileCache;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\routing\CliController;

$report = Report::createSuccess('Initialize tao application');

$serviceManager = ServiceManager::getServiceManager();
$serviceManager->get(SetupFileCache::class)->createDirectory(GENERIS_CACHE_PATH);
$report->add(Report::createSuccess('Init filesystem complete.'));

$actionList = [
    tools\ContainerCacheWarmup::class => [],
    tools\ApplicationCacheWarmup::class => [],
];

$cliController = new CliController();
$cliController->setServiceLocator($serviceManager);

foreach ($actionList as $actionIdentifier => $actionParams) {
    $report->add($cliController->runAction($actionIdentifier, $actionParams));
}

echo helpers_Report::renderToCommandline($report);
