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
 */

declare(strict_types=1);

namespace oat\tao\unit\test\model\featureFlag;

use oat\generis\test\TestCase;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use PHPUnit\Framework\MockObject\MockObject;

class FeatureFlagCheckerTest extends TestCase
{
    /** @var FeatureFlagChecker */
    private $subject;

    /** @var SimpleCache|MockObject */
    private $simpleCache;

    public function setUp(): void
    {
        $this->simpleCache = $this->createMock(SimpleCache::class);
        $this->subject = new FeatureFlagChecker();
        $this->subject->setServiceManager(
            $this->getServiceLocatorMock(
                [
                    SimpleCache::SERVICE_ID => $this->simpleCache
                ]
            )
        );

        $_ENV['FEATURE'] = true;
        $_ENV['FEATURE_DISABLED'] = false;
    }

    public function testIsEnabled(): void
    {
        self::assertTrue($this->subject->isEnabled('FEATURE'));
    }

    public function testIsNotEnabled(): void
    {
        $this->simpleCache
            ->method('has')
            ->willReturn(true);

        $this->simpleCache
            ->method('get')
            ->willReturn(false);

        self::assertFalse($this->subject->isEnabled('NOT_ENABLED_FEATURE'));
    }

    public function testIsEnabledOnDisabledFeature(): void
    {
        self::assertFalse($this->subject->isEnabled('FEATURE_DISABLED'));
    }
}
