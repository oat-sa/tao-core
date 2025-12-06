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

use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use oat\generis\test\IteratorMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_elements_xhtml_Combobox;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use oat\tao\model\Lists\Presentation\Web\Factory\DependsOnPropertyFormFieldFactory;
use oat\generis\model\resource\DependsOnPropertyCollection as GenerisDependsOnPropertyCollection;

class DependsOnPropertyFormFieldFactoryTest extends TestCase
{
    use IteratorMockTrait;

    /** @var DependsOnPropertyFormFieldFactory */
    private $sut;

    /** @var FeatureFlagChecker|MockObject */
    private $featureFlagChecker;

    /** @var DependsOnPropertyRepository|MockObject */
    private $dependsOnPropertyRepository;

    /** @var tao_helpers_form_elements_xhtml_Combobox|MockObject */
    private $element;

    protected function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->dependsOnPropertyRepository = $this->createMock(DependsOnPropertyRepository::class);

        $this->sut = new DependsOnPropertyFormFieldFactory(
            $this->featureFlagChecker,
            $this->dependsOnPropertyRepository
        );

        $this->element = $this->createMock(tao_helpers_form_elements_xhtml_Combobox::class);
        $this->sut->withElement($this->element);
    }

    public function testWillNotCreateWithEmptyCollection(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $dependsOnProperty1 = $this->createMock(DependsOnProperty::class);
        $dependsOnProperty1
            ->expects($this->once())
            ->method('getUriEncoded')
            ->willReturn('uri1');
        $dependsOnProperty1
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('property label 1');

        $dependsOnProperty2 = $this->createMock(DependsOnProperty::class);
        $dependsOnProperty2
            ->expects($this->once())
            ->method('getUriEncoded')
            ->willReturn('uri2');
        $dependsOnProperty2
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('property label 2');

        $dependsOnPropertyCollection = $this->createIteratorMock(
            DependsOnPropertyCollection::class,
            [
                $dependsOnProperty1,
                $dependsOnProperty2,
            ]
        );

        $this->dependsOnPropertyRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($dependsOnPropertyCollection);

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->element
            ->expects($this->never())
            ->method('setValue');
        $this->element
            ->expects($this->once())
            ->method('setOptions');

        $this->sut->create(
            [
                DependsOnPropertyFormFieldFactory::OPTION_INDEX => 0,
                DependsOnPropertyFormFieldFactory::OPTION_PROPERTY => $property,
            ]
        );
    }

    public function testCreateWithPrimaryProperty(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $primaryProperty = $this->createMock(core_kernel_classes_Property::class);
        $primaryProperty
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('primaryPropertyUri');
        $primaryProperty
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('primaryPropertyLabel');

        $dependsOnPropertyCollection = $this->createIteratorMock(
            GenerisDependsOnPropertyCollection::class,
            [
                $primaryProperty,
            ]
        );

        $secondaryProperty = $this->createMock(core_kernel_classes_Property::class);
        $secondaryProperty
            ->expects($this->once())
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        $this->dependsOnPropertyRepository
            ->expects($this->never())
            ->method('findAll');

        $this->element
            ->expects($this->once())
            ->method('setValue');
        $this->element
            ->expects($this->once())
            ->method('setOptions');

        $this->sut->create(
            [
                DependsOnPropertyFormFieldFactory::OPTION_INDEX => 0,
                DependsOnPropertyFormFieldFactory::OPTION_PROPERTY => $secondaryProperty,
            ]
        );
    }
}
