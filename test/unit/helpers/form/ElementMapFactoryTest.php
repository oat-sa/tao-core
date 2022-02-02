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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\helpers\test\unit\helpers\form;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\test\TestCase;
use oat\tao\helpers\form\ElementMapFactory;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\PresortedListSpecification;
use oat\tao\test\Asset\CustomRootClassFixture;
use tao_helpers_form_elements_Authoring;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_FormElement;

class ElementMapFactoryTest extends TestCase
{
    /** @var ElementMapFactory */
    private $sut;

    /** @var tao_helpers_form_FormElement|MockObject */
    private $elementMock;

    public function setUp(): void
    {
        $this->sut = new ElementMapFactory();
        $this->elementMock = $this->createMock(tao_helpers_form_FormElement::class);
    }

    /**
     * @dataProvider somethingDataProvider
     */
    public function testSomething(
        ?\tao_helpers_form_FormElement $expected,
        bool $standalone,
        bool $listDependencyEnabled,
        core_kernel_classes_Property $property,
        ?tao_helpers_form_FormElement $elementForWidget
    ): void
    {
        $ffChecker = $this->createMock(FeatureFlagChecker::class);
        $ffChecker
            ->method('isEnabled')
            ->with(FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED)
            ->willReturn($listDependencyEnabled);

        $this->sut->withStandaloneModel($standalone);
        $this->sut->withElement($elementForWidget);

        $element = $this->sut->create($property);

        if($expected === null) {
            $this->assertNull($element);
            return;
        }

        $this->assertEquals($expected->getName(), $element->getName());

    }

    public function somethingDataProvider(): array
    {
        return [
            'A property with no widget returns a null element' => [
                'expected' => null,
                'standalone' => false,
                'listDependencyEnabled' => false,
                'property' => $this->getMockProperty(null, true),
                'elementForWidget' => null,
            ],
            'Authoring widget returns null in standalone mode' => [
                'expected' => null,
                'standalone' => true,
                'listDependencyEnabled' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    true
                ),
                'elementForWidget' => null,
            ],
            // @todo Test to cover the implicit conversion
            //       AsyncFile::WIDGET_ID -> GenerisAsyncFile::WIDGET_ID
            'Having no element for the widget returns null' => [
                'expected' => null,
                'standalone' => false,
                'listDependencyEnabled' => false,
                'property' => $this->getMockProperty(
                    'hello-world',
                    true
                ),
                'elementForWidget' => $this->elementMock,
            ],
        ];
    }

    private function getMockProperty(
        ?string $widgetType,
        bool $isList,
        bool $isClass = true,
        string $rangeObjectType = core_kernel_classes_Class::class
    ): core_kernel_classes_Property
    {
        $rangeClass = $this->createMock($rangeObjectType);

        if (is_a($rangeClass, core_kernel_classes_Resource::class)) {
            $rangeClass
                ->method('isClass')
                ->willReturn($isClass);
        }

        if (is_a($rangeClass, core_kernel_classes_Class::class)) {
            $rangeClass
                ->method('isSubClassOf')
                ->willReturn($isList);
        }

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->method('getRange')
            ->withAnyParameters()
            ->willReturn($rangeClass);

        if ($widgetType) {
            $widgetMock = $this->createMock(core_kernel_classes_Property::class);
            $widgetMock
                ->method('getUri')
                ->willReturn($widgetType);
        }

        $property
            ->method('getWidget')
            ->willReturn($widgetType ? $widgetMock : null);

        return $property;
    }
}
