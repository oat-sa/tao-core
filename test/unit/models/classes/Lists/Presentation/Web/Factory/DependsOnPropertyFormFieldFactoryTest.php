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

namespace oat\tao\test\unit\model\Lists\Presentation\Web\Factory;

use core_kernel_classes_Property;
use oat\generis\test\TestCase;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\Lists\Business\Domain\DependencyPropertyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use oat\tao\model\Lists\Presentation\Web\Factory\DependsOnPropertyFormFieldFactory;
use PHPUnit\Framework\MockObject\MockObject;

class DependsOnPropertyFormFieldFactoryTest extends TestCase
{
    /** @var DependsOnPropertyFormFieldFactory */
    private $sut;

    /** @var FeatureFlagChecker|MockObject */
    private $featureFlagChecker;

    /** @var DependsOnPropertyRepository|MockObject */
    private $dependsOnPropertyRepository;

    public function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->dependsOnPropertyRepository = $this->createMock(DependsOnPropertyRepository::class);

        $this->sut = new DependsOnPropertyFormFieldFactory();
        $this->sut->setServiceLocator(
            $this->getServiceLocatorMock(
                [
                    FeatureFlagChecker::class => $this->featureFlagChecker,
                    DependsOnPropertyRepository::class => $this->dependsOnPropertyRepository,

                ]
            )
        );
    }

    public function testWillNotCreateWithEmptyCollection(): void
    {
        $collection = new DependencyPropertyCollection();
        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn(true);

        $this->dependsOnPropertyRepository
            ->method('findAll')
            ->willReturn($collection);

        $this->assertNull(
            $this->sut->create(
                [
                    'index' => 0,
                    'property' => $property,
                ]
            )
        );
    }
}
