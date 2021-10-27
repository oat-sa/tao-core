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
use oat\generis\test\TestCase;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use tao_helpers_form_FormElement;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\helpers\form\elements\ElementValue;
use oat\generis\model\resource\DependsOnPropertyCollection;
use oat\tao\model\Lists\Business\Validation\DependsOnPropertyValidator;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;

class DependsOnPropertyValidatorTest extends TestCase
{
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
     * @param array|string $values
     */
    public function testEvaluate(bool $expected, array $childListItemsUris, $values): void
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getDependsOnPropertyCollection')
            ->willReturn($this->createDependsOnPropertyCollection());

        $this->ontology
            ->method('getProperty')
            ->willReturn($property);

        $this->dependencyRepository
            ->method('findChildListItemsUris')
            ->willReturn($childListItemsUris);

        $this->sut->setElement($this->createElementMock());
        $this->sut->acknowledge($this->createFormMock());

        $this->assertEquals($expected, $this->sut->evaluate($values));
    }

    public function dataProvider(): array
    {
        $objectElement = new ElementValue('Child URI', 'Child Label');

        return [
            'True - String value' => [
                'expected' => true,
                'childListItemsUris' => [
                    'Child URI',
                ],
                'values' => 'Child URI',
            ],
            'True - Single value' => [
                'expected' => true,
                'childListItemsUris' => [
                    'Child URI',
                ],
                'values' => [
                    'Child URI',
                ],
            ],
            'True - Multiple values' => [
                'expected' => true,
                'childListItemsUris' => [
                    'Child URI 1',
                    'Child URI 3',
                    'Child URI 2',
                ],
                'values' => [
                    'Child URI 1',
                    'Child URI 2',
                    'Child URI 3',
                ],
            ],
            'True - Object value' => [
                'expected' => true,
                'childListItemsUris' => [
                    'Child URI',
                ],
                'values' => [
                    $objectElement,
                ],
            ],
            'False - String value' => [
                'expected' => false,
                'childListItemsUris' => [
                    'Other Child URI',
                ],
                'values' => 'Child URI',
            ],
            'False - Single value' => [
                'expected' => false,
                'childListItemsUris' => [
                    'Other Child URI',
                ],
                'values' => [
                    'Child URI',
                ],
            ],
            'False - Multiple values' => [
                'expected' => false,
                'childListItemsUris' => [
                    'Child URI 1',
                    'Child URI 3',
                    'Other Child URI 2',
                ],
                'values' => [
                    'Child URI 1',
                    'Child URI 2',
                    'Child URI 3',
                ],
            ],
            'False - Object value' => [
                'expected' => false,
                'childListItemsUris' => [
                    'Other Child URI',
                ],
                'values' => [
                    $objectElement,
                ],
            ],
        ];
    }

    private function createDependsOnPropertyCollection(): DependsOnPropertyCollection
    {
        $range = $this->createMock(core_kernel_classes_Class::class);
        $range
            ->method('getUri')
            ->willReturn('rangeUri');

        $parentProperty = $this->createMock(core_kernel_classes_Property::class);
        $parentProperty
            ->method('getUri')
            ->willReturn('parentUri');
        $parentProperty
            ->method('getRange')
            ->willReturn($range);

        $dependsOnPropertyCollection = new DependsOnPropertyCollection();
        $dependsOnPropertyCollection->append($parentProperty);

        return $dependsOnPropertyCollection;
    }

    private function createFormMock(): tao_helpers_form_Form
    {
        $form = $this->getMockForAbstractClass(
            tao_helpers_form_Form::class,
            [],
            '',
            false,
            true,
            true,
            ['getElement']
        );
        $form
            ->method('getElement')
            ->willReturn($this->createElementMock());

        return $form;
    }

    private function createElementMock():  tao_helpers_form_FormElement
    {
        $element = $this->getMockForAbstractClass(
            tao_helpers_form_FormElement::class,
            [],
            '',
            false,
            true,
            true,
            [
                'getInputValue',
                'getName',
            ]
        );
        $element
            ->method('getInputValue')
            ->willReturn('uri');
        $element
            ->method('getName')
            ->willReturn('name');

        return $element;
    }
}
