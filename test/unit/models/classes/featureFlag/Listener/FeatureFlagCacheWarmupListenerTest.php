<?php

/*
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
 *
 */

namespace oat\tao\unit\test\model\featureFlag\Listener;

use oat\generis\model\data\event\CacheWarmupEvent;
use oat\oatbox\reporting\Report;
use oat\tao\model\featureFlag\Listener\FeatureFlagCacheWarmupListener;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;
use PHPUnit\Framework\TestCase;

class FeatureFlagCacheWarmupListenerTest extends TestCase
{
    /**
     * @var FeatureFlagRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $featureFlagRepoMock;
    private FeatureFlagCacheWarmupListener $listener;

    protected function setUp(): void
    {
        $this->featureFlagRepoMock = $this->createMock(FeatureFlagRepositoryInterface::class);

        $this->listener = new FeatureFlagCacheWarmupListener(
            $this->featureFlagRepoMock
        );
    }

    public function testHandleEvent(): void
    {
        $this->featureFlagRepoMock
            ->expects($this->once())
            ->method('list');

        $this->listener->handleEvent(new CacheWarmupEvent());
    }

    public function testReportCreated(): void
    {
        $testEvent = new CacheWarmupEvent();

        $this->listener->handleEvent($testEvent);

        $reports = $testEvent->getReports();
        $this->assertCount(1, $reports);
        $this->assertInstanceOf(Report::class, $reports[0]);
        $this->assertEquals('Generated feature flags cache.', $reports[0]->getMessage());
    }
}
