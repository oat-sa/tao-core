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
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertyCollection;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;
use oat\tao\model\Lists\Presentation\Web\Factory\DependsOnPropertyFormFieldFactory;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_elements_xhtml_Combobox;

class DependsOnPropertyFormFieldFactoryTest extends TestCase
{
    /** @var DependsOnPropertyFormFieldFactory */
    private $sut;

    /** @var FeatureFlagChecker|MockObject */
    private $featureFlagChecker;

    /** @var DependsOnPropertyRepository|MockObject */
    private $dependsOnPropertyRepository;

    /** @var tao_helpers_form_elements_xhtml_Combobox|MockObject */
    private $element;

    public function setUp(): void
    {
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->dependsOnPropertyRepository = $this->createMock(DependsOnPropertyRepository::class);
        $this->element = $this->createMock(tao_helpers_form_elements_xhtml_Combobox::class);

        $this->sut = new DependsOnPropertyFormFieldFactory();
        $this->sut->withElement($this->element);
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
        $property1 = $this->createMock(core_kernel_classes_Property::class);
        $property1->method('getUri')
            ->willReturn('uri1');
        $property1->method('getLabel')
            ->willReturn('property label 2');

        $property2 = $this->createMock(core_kernel_classes_Property::class);
        $property2->method('getUri')
            ->willReturn('uri2');
        $property2->method('getLabel')
            ->willReturn('property label 2');

        $collection = new DependsOnPropertyCollection();
        $collection->append(new DependsOnProperty($property1));
        $collection->append(new DependsOnProperty($property2));

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->featureFlagChecker
            ->method('isEnabled')
            ->willReturn(true);

        $this->dependsOnPropertyRepository
            ->method('findAll')
            ->willReturn($collection);

        $element = $this->sut->create(
            [
                'index' => 0,
                'property' => $property,
            ]
        );

        $this->assertInstanceOf(tao_helpers_form_elements_xhtml_Combobox::class, $element);
    }
}
