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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use oat\generis\model\data\event\CacheWarmupEvent;
use oat\generis\model\data\event\ResourceUpdated;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\reporting\Report;
use oat\tao\model\featureFlag\Listener\FeatureFlagCacheWarmupListener;
use oat\tao\model\Language\Listener\LanguageCacheWarmupListener;
use oat\tao\model\listener\ClassPropertiesChangedListener;
use oat\tao\model\listener\ClassPropertyCacheWarmupListener;
use oat\tao\model\menu\Listener\MenuCacheWarmupListener;
use oat\tao\model\migrations\MigrationsService;
use oat\tao\model\routing\Listener\AnnotationCacheWarmupListener;

/**
 * Class RegisterEvents
 * @package oat\tao\scripts\install
 */
class RegisterEvents extends InstallAction
{
    use OntologyAwareTrait;

    public function __invoke($params)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(
            \common_ext_event_ExtensionInstalled::class,
            [MigrationsService::SERVICE_ID, 'extensionInstalled']
        );
        $eventManager->attach(
            CacheWarmupEvent::class,
            [AnnotationCacheWarmupListener::class, 'handleEvent']
        );
        $eventManager->attach(
            CacheWarmupEvent::class,
            [FeatureFlagCacheWarmupListener::class, 'handleEvent']
        );
        $eventManager->attach(
            CacheWarmupEvent::class,
            [LanguageCacheWarmupListener::class, 'handleEvent']
        );
        $eventManager->attach(
            CacheWarmupEvent::class,
            [MenuCacheWarmupListener::class, 'handleEvent']
        );
        $eventManager->attach(
            CacheWarmupEvent::class,
            [ClassPropertyCacheWarmupListener::class, 'handleEvent']
        );
        $eventManager->attach(
            ResourceUpdated::class,
            [ClassPropertiesChangedListener::SERVICE_ID, 'handleUpdatedEvent']
        );

        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        return Report::createSuccess('Events registered');
    }
}
