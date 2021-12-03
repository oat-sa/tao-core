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

declare(strict_types=1);

namespace oat\tao\test\unit\AdvancedSearch;

use oat\generis\test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagListService;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;

class FeatureFlagListServiceTest extends TestCase
{
    /** @var FeatureFlagListService */
    private $sut;

    /** @var FeatureFlagCheckerInterface|MockObject */
    private $featureFlagChecker;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);

        $this->sut = new FeatureFlagListService($this->featureFlagChecker);
    }

    public function testList(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->assertEquals(
            [
                FeatureFlagChecker::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED => true,
            ],
            $this->sut->list()
        );
    }
}
