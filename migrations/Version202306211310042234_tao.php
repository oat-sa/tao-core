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
 * Copyright (c) 2023(original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\generis\model\data\event\CacheWarmupEvent;
use oat\oatbox\event\EventManager;
use oat\tao\model\featureFlag\Listener\FeatureFlagCacheWarmupListener;
use oat\tao\model\Language\Listener\LanguageCacheWarmupListener;
use oat\tao\model\menu\Listener\MenuCacheWarmupListener;
use oat\tao\model\routing\Listener\AnnotationCacheWarmupListener;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Migration to register cache warmup listeners
 */
final class Version202306211310042234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Register cache warmup listeners.';
    }

    public function up(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(CacheWarmupEvent::class, [AnnotationCacheWarmupListener::class, 'handleEvent']);
        $eventManager->attach(CacheWarmupEvent::class, [FeatureFlagCacheWarmupListener::class, 'handleEvent']);
        $eventManager->attach(CacheWarmupEvent::class, [LanguageCacheWarmupListener::class, 'handleEvent']);
        $eventManager->attach(CacheWarmupEvent::class, [MenuCacheWarmupListener::class, 'handleEvent']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }

    public function down(Schema $schema): void
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->detach(CacheWarmupEvent::class, [AnnotationCacheWarmupListener::class, 'handleEvent']);
        $eventManager->detach(CacheWarmupEvent::class, [FeatureFlagCacheWarmupListener::class, 'handleEvent']);
        $eventManager->detach(CacheWarmupEvent::class, [LanguageCacheWarmupListener::class, 'handleEvent']);
        $eventManager->detach(CacheWarmupEvent::class, [MenuCacheWarmupListener::class, 'handleEvent']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }
}
