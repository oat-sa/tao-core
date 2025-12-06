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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Lists\Business\Service;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Service\DependsOnPropertySynchronizer;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertySynchronizerContext;

class DependsOnPropertySynchronizerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private DependsOnPropertySynchronizer $sut;
    private FeatureFlagCheckerInterface|MockObject $featureFlagChecker;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);

        $this->sut = new DependsOnPropertySynchronizer();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock([
                FeatureFlagChecker::class => $this->featureFlagChecker,
            ])
        );
    }

    public function testFeatureFlagDisabled(): void
    {
        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn(false);

        $context = $this->createMock(DependsOnPropertySynchronizerContext::class);
        $context
            ->expects($this->never())
            ->method('getParameter');

        $this->sut->sync($context);
    }
}
