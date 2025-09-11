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

namespace oat\tao\test\unit\model\Lists\Business\Validation;

use tao_helpers_form_Form;
use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use tao_helpers_form_FormElement;
use oat\generis\model\data\Ontology;
use oat\generis\test\IteratorMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\helpers\form\elements\ElementValue;
use oat\tao\helpers\form\elements\AbstractSearchElement;
use oat\generis\model\resource\DependsOnPropertyCollection;
use oat\tao\model\Lists\Business\Validation\DependsOnPropertyValidator;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;

class DependsOnPropertyValidatorTest extends TestCase
{
    use IteratorMockTrait;

    private const DECODED_PROPERTY = 'https://test.test/property#identifier';
    private const ENCODED_PROPERTY = 'https_2_test_0_test_1_property_3_identifier';

    private const DECODED_PRIMARY_PROPERTY = 'https://test.test/primaryProperty#identifier';
    private const ENCODED_PRIMARY_PROPERTY = 'https_2_test_0_test_1_primaryProperty_3_identifier';

    private const DECODED_PRIMARY_VALUE = 'https://test.test/primaryValue#identifier';

    /** @var DependencyRepositoryInterface|MockObject */
    private $dependencyRepository;

    /** @var DependsOnPropertyValidator */
    private $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    protected function setUp(): void
    {
        $this->dependencyRepository = $this->createMock(DependencyRepositoryInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new DependsOnPropertyValidator($this->dependencyRepository, $this->ontology);
    }

    public function testIsPreValidationRequired(): void
    {
        $this->assertTrue($this->sut->isPreValidationRequired());
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string|string[] $values
     * @param string[] $childListItemsUris
     */
    public function testEvaluate(
        bool $expected,
        $values,
        array $childListItemsUris,
        bool $asElementValues = false
    ): void {
        $secondaryElement = $this->getElementMock(self::ENCODED_PROPERTY, $values, $asElementValues);
        $this->sut->setElement($secondaryElement);

        $primaryProperty = $this->getPrimaryPropertyMock();
        $dependsOnPropertyCollection = $this->createIteratorMock(
            DependsOnPropertyCollection::class,
            [$primaryProperty]
        );
        $secondaryProperty = $this->getSecondaryPropertyMock($dependsOnPropertyCollection);

        $this->configureOntology($primaryProperty, $secondaryProperty);

        $primaryElement = $this->getElementMock(
            self::ENCODED_PRIMARY_PROPERTY,
            self::DECODED_PRIMARY_VALUE,
            false
        );
        $form = $this->getFormMock($primaryElement, $values);
        $this->sut->acknowledge($form);

        $this->configureDependencyRepository($childListItemsUris);

        $this->assertEquals($expected, $this->sut->evaluate($values));
    }

    public function dataProvider(): array
    {
        $decodedValueOne = 'https://test.test/valueOne#identifier';
        $decodedValueTwo = 'https://test.test/valueTwo#identifier';

        return [
            'True - Single value' => [
                'expected' => true,
                'values' => $decodedValueOne,
                'childListItemsUris' => [
                    $decodedValueOne,
                ],
            ],
            'True - Multiple values' => [
                'expected' => true,
                'values' => [
                    $decodedValueOne,
                    $decodedValueTwo,
                ],
                'childListItemsUris' => [
                    $decodedValueOne,
                    $decodedValueTwo,
                ],
            ],
            'True - Object value' => [
                'expected' => true,
                'values' => [
                    $decodedValueOne,
                ],
                'childListItemsUris' => [
                    $decodedValueOne,
                ],
                'asElementValues' => true,
            ],
            'False - Single value' => [
                'expected' => false,
                'values' => $decodedValueOne,
                'childListItemsUris' => [
                    'Other URI',
                ],
            ],
            'False - Multiple values' => [
                'expected' => false,
                'values' => [
                    $decodedValueOne,
                    $decodedValueTwo,
                ],
                'childListItemsUris' => [
                    $decodedValueOne,
                    'Other URI',
                ],
            ],
            'False - Object value' => [
                'expected' => false,
                'values' => [
                    $decodedValueOne,
                ],
                'childListItemsUris' => [
                    'Other URI',
                ],
                'asElementValues' => true,
            ],
        ];
    }

    /**
     * @param string|string[] $values
     */
    private function getElementMock(string $name, $values, bool $asElementValues): tao_helpers_form_FormElement
    {
        return $asElementValues
            ? $this->getSearchElementMock($name, $values)
            : $this->getFormElementMock($name, $values);
    }

    /**
     * @param string|string[] $values
     */
    private function getFormElementMock(string $name, $values): tao_helpers_form_FormElement
    {
        $element = $this->createMock(tao_helpers_form_FormElement::class);

        $element
            ->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($name);
        $element
            ->expects($this->once())
            ->method('getInputValue')
            ->willReturn(is_array($values) ? implode(',', $values) : $values);
        $element
            ->expects($this->never())
            ->method('getRawValue');

        return $element;
    }

    private function getSearchElementMock(string $name, array $values): AbstractSearchElement
    {
        $element = $this->createMock(AbstractSearchElement::class);

        $element
            ->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($name);
        $element
            ->expects($this->once())
            ->method('getInputValue')
            ->willReturn(null);
        $element
            ->expects($this->once())
            ->method('getValues')
            ->willReturn(
                array_map(
                    static function (string $uri) {
                        return new ElementValue($uri, 'Label');
                    },
                    $values
                )
            );
        $element
            ->expects($this->never())
            ->method('getRawValue');

        return $element;
    }

    private function getPrimaryPropertyMock(): core_kernel_classes_Property
    {
        $primaryPropertyRange = $this->createMock(core_kernel_classes_Class::class);

        $primaryPropertyRange
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('primaryPropertyRangeUri');

        $primaryProperty = $this->createMock(core_kernel_classes_Property::class);

        $primaryProperty
            ->expects($this->once())
            ->method('getUri')
            ->willReturn(self::DECODED_PRIMARY_PROPERTY);
        $primaryProperty
            ->expects($this->once())
            ->method('getRange')
            ->willReturn($primaryPropertyRange);
        $primaryProperty
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        return $primaryProperty;
    }

    /**
     * @param DependsOnPropertyCollection|MockObject $dependsOnPropertyCollection
     */
    private function getSecondaryPropertyMock(
        DependsOnPropertyCollection $dependsOnPropertyCollection
    ): core_kernel_classes_Property {
        $secondaryProperty = $this->createMock(core_kernel_classes_Property::class);

        $secondaryProperty
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);
        $secondaryProperty
            ->expects($this->once())
            ->method('getDependsOnPropertyCollection')
            ->willReturn($dependsOnPropertyCollection);

        return $secondaryProperty;
    }

    private function configureOntology(
        core_kernel_classes_Property $primaryProperty,
        core_kernel_classes_Property $secondaryProperty
    ): void {
        $this->ontology
            ->expects($this->exactly(2))
            ->method('getProperty')
            ->willReturnCallback(
                function (string $propertyUri) use (
                    $primaryProperty,
                    $secondaryProperty
                ): core_kernel_classes_Property {
                    if ($propertyUri === self::DECODED_PRIMARY_PROPERTY) {
                        return $primaryProperty;
                    }

                    if ($propertyUri === self::DECODED_PROPERTY) {
                        return $secondaryProperty;
                    }

                    return $this->createMock(core_kernel_classes_Property::class);
                }
            );
        $this->ontology
            ->expects($this->never())
            ->method('getClass');
    }

    /**
     * @param string|string[] $values
     */
    private function getFormMock(tao_helpers_form_FormElement $primaryElement, $values): tao_helpers_form_Form
    {
        $form = $this->createMock(tao_helpers_form_Form::class);

        $form
            ->expects($this->exactly(3))
            ->method('getValues')
            ->willReturn([
                self::ENCODED_PROPERTY => $values,
                self::ENCODED_PRIMARY_PROPERTY => self::DECODED_PRIMARY_VALUE,
            ]);
        $form
            ->expects($this->once())
            ->method('getElement')
            ->willReturn($primaryElement);

        return $form;
    }

    /**
     * @param string[] $childListItemsUris
     */
    private function configureDependencyRepository(array $childListItemsUris): void
    {
        $this->dependencyRepository
            ->method('findChildListItemsUris')
            ->willReturn($childListItemsUris);
    }
}
