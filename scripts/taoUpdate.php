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
 * Copyright (c) 2014-2021 (original work) Open Assessment Technologies SA;
 */

require_once dirname(__FILE__) . '/../includes/raw_start.php';

use oat\oatbox\cache\SetupFileCache;
use oat\oatbox\reporting\Report;
use oat\tao\model\extension\UpdateExtensions;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;

$serviceManager = ServiceManager::getServiceManager();

$serviceManager->get(SetupFileCache::class)->createDirectory(GENERIS_CACHE_PATH);
$serviceManager->get(SetupFileCache::class)->createDirectory(GENERIS_CACHE_INSTALL_DI_PATH);

$action = new UpdateExtensions();
$action->setServiceLocator($serviceManager);
$report = $action->__invoke([]);

$serviceManager->rebuildContainer();

/** @var FeatureFlagRepositoryInterface $featureFlagChecker */
$featureFlagChecker = $serviceManager->getContainer()->get(FeatureFlagRepositoryInterface::class);
$featureFlagChecker->clearCache();

$report->add(Report::createSuccess('Update completed'));
$report->add(Report::createSuccess('Dependency Injection Container rebuilt'));
$report->add(Report::createSuccess('FeatureFlag cache cleared'));

echo helpers_Report::renderToCommandline($report);
