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
use oat\oatbox\AbstractRegistry;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\form\ElementMapFactory;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Language\Business\Specification\LanguageClassSpecification;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Specification\PresortedListSpecification;
use oat\tao\test\Asset\CustomRootClassFixture;
use tao_helpers_form_elements_Authoring;
use tao_helpers_form_elements_MultipleElement;
use tao_helpers_form_FormElement;
use PHPUnit\Framework\MockObject\MockObject;
use tao_helpers_form_elements_AsyncFile;
use tao_helpers_form_elements_GenerisAsyncFile;

class ElementMapFactoryTest extends TestCase
{
    /** @var ElementMapFactory */
    private $sut;

    /** @var AbstractRegistry|MockObject */
    private $ruleRegistry;

    /** @var FeatureFlagCheckerInterface|MockObject */
    private $ffChecker;

    /** @var LanguageClassSpecification|MockObject */
    private $languageClassSpecification;

    /** @var ValueCollectionService|MockObject */
    private $valueCollectionService;

    public function setUp(): void
    {
        $this->ffChecker = $this->createMock(FeatureFlagChecker::class);
        $this->ruleRegistry = $this->createMock(ValidationRuleRegistry::class);
        $this->languageClassSpecification = $this->createMock(LanguageClassSpecification::class);
        $this->valueCollectionService = $this->createMock(ValueCollectionService::class);

        $this->sut = new ElementMapFactory();

        $this->sut->withFeatureFlagChecker($this->ffChecker);
        $this->sut->withValidationRuleRegistry($this->ruleRegistry);
        $this->sut->withLanguageClassSpecification($this->languageClassSpecification);
    }

    /**
     * @dataProvider successScenariosDataProvider
     */
    public function testSuccessScenario(
        ?tao_helpers_form_FormElement $expected,
        array $expectedOptions,
        bool $standalone,
        bool $listDependencyEnabled,
        bool $statisticMetadataEnabled,
        core_kernel_classes_Property $property,
        ?tao_helpers_form_FormElement $elementForWidget,
        array $validators,
        ?core_kernel_classes_Resource $instance = null
    ): void {
        $this->ffChecker
            ->method('isEnabled')
            ->willReturnMap(
                [
                    [
                        FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED,
                        $listDependencyEnabled
                    ],
                    [
                        FeatureFlagCheckerInterface::FEATURE_FLAG_STATISTIC_METADATA_IMPORT,
                        $statisticMetadataEnabled,
                    ],
                ]
            );

        $valueCollection = $this->createMock(ValueCollection::class);

        $this->valueCollectionService
            ->method('findAll')
            ->withAnyParameters()
            ->willReturn($valueCollection);

        $this->ruleRegistry
            ->method('getValidators')
            ->with($property)
            ->willReturn($validators);

        if ($instance !== null) {
            $this->sut->withInstance($instance);
        }

        $this->sut->withStandaloneMode($standalone);
        $this->sut->withElement($elementForWidget);
        $this->sut->withValueCollectionService($this->valueCollectionService);

        $elementForWidget
            ->expects($this->atLeastOnce())
            ->method('setDescription')
            ->with($expected->getDescription());

        if ($elementForWidget instanceof tao_helpers_form_elements_MultipleElement) {
            $elementForWidget
                ->expects($this->atLeastOnce())
                ->method('setOptions')
                ->with($expectedOptions);
        }

        $element = $this->sut->create($property);

        $this->assertInstanceOf(tao_helpers_form_FormElement::class, $element);
    }

    public function successScenariosDataProvider(): array
    {
        return [
            'Happy Path' => [
                'expected' => $this->mockNamedElement(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    'property label'
                ),
                'expectedOptions' => [

                ],
                'standalone' => false,
                'listDependencyEnabled' => false,
                'statisticMetadataEnabled' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    true,
                    'property label'
                ),
                'elementForWidget' => $this->mockElementForWidget(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    tao_helpers_form_elements_MultipleElement::class
                ),
                'validators' => [],
            ],
            'Happy Path with instance' => [
                'expected' => $this->mockNamedElement(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    'property label'
                ),
                'expectedOptions' => [

                ],
                'standalone' => false,
                'listDependencyEnabled' => false,
                'statisticMetadataEnabled' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    true,
                    'property label'
                ),
                'elementForWidget' => $this->mockElementForWidget(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    tao_helpers_form_elements_MultipleElement::class
                ),
                'validators' => [],
                'instance' => $this->createMock(
                    core_kernel_classes_Resource::class
                ),
            ],
            'AsyncFile is implicitly converted to GenerisAsyncFile' => [
                'expected' => $this->mockNamedElement(
                    tao_helpers_form_elements_GenerisAsyncFile::WIDGET_ID,
                    'property label'
                ),
                'expectedOptions' => [

                ],
                'standalone' => false,
                'listDependencyEnabled' => false,
                'statisticMetadataEnabled' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_AsyncFile::WIDGET_ID,
                    true,
                    'property label'
                ),
                'elementForWidget' => $this->mockElementForWidget(
                    tao_helpers_form_elements_GenerisAsyncFile::WIDGET_ID,
                    tao_helpers_form_elements_MultipleElement::class
                ),
                'validators' => [],
            ],
        ];
    }

    /**
     * @dataProvider failureScenariosDataProvider
     */
    public function testFailureScenario(
        bool $standalone,
        bool $listDependencyEnabled,
        bool $statisticMetadataEnabled,
        core_kernel_classes_Property $property,
        ?tao_helpers_form_FormElement $elementForWidget,
        array $validators
    ): void {
        $this->ffChecker
            ->method('isEnabled')
            ->willReturnMap(
                [
                    [
                        FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED,
                        $listDependencyEnabled
                    ],
                    [
                        FeatureFlagCheckerInterface::FEATURE_FLAG_STATISTIC_METADATA_IMPORT,
                        $statisticMetadataEnabled,
                    ],
                ]
            );

        $this->ruleRegistry
            ->method('getValidators')
            ->with($property)
            ->willReturn($validators);

        $this->sut->withStandaloneMode($standalone);
        $this->sut->withElement($elementForWidget);
        $this->sut->withFeatureFlagChecker($this->ffChecker);
        $this->sut->withValidationRuleRegistry($this->ruleRegistry);

        $element = $this->sut->create($property);
        $this->assertNull($element);
    }

    public function failureScenariosDataProvider(): array
    {
        return [
            'A property with no widget returns a null element' => [
                'standalone' => false,
                'listDependencyEnabled' => false,
                'statisticMetadataEnabled' => false,
                'property' => $this->getMockProperty(
                    null,
                    true,
                    'the label'
                ),
                'elementForWidget' => null,
                'validators' => [],
            ],
            'Authoring widget returns null in standalone mode' => [
                'standalone' => true,
                'listDependencyEnabled' => false,
                'statisticMetadataEnabled' => false,
                'property' => $this->getMockProperty(
                    tao_helpers_form_elements_Authoring::WIDGET_ID,
                    true,
                    'the label'
                ),
                'elementForWidget' => null,
                'validators' => [],
            ],

            'Having no element for the widget returns null' => [
                'standalone' => false,
                'listDependencyEnabled' => false,
                'statisticMetadataEnabled' => false,
                'property' => $this->getMockProperty(
                    'hello-world',
                    true,
                    'the label'
                ),
                'elementForWidget' => null,
                'validators' => [],
            ],
        ];
    }

    private function mockNamedElement(
        string $name,
        string $description
    ): tao_helpers_form_FormElement {
        $elementMock = $this->createMock(tao_helpers_form_FormElement::class);
        $elementMock
            ->method('getName')
            ->willReturn($name);
        $elementMock
            ->method('getDescription')
            ->willReturn($description);

        return $elementMock;
    }

    private function mockElementForWidget(
        string $widgetId,
        string $className = tao_helpers_form_FormElement::class
    ): tao_helpers_form_FormElement {
        $elementMock = $this->createMock($className);
        $elementMock
            ->method('getWidget')
            ->willReturn($widgetId);

        return $elementMock;
    }

    private function getMockProperty(
        ?string $widgetType,
        bool $isList,
        string $label,
        bool $isClass = true,
        string $rangeObjectType = core_kernel_classes_Class::class
    ): core_kernel_classes_Property {
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

        $property
            ->method('getLabel')
            ->willReturn($label);

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
