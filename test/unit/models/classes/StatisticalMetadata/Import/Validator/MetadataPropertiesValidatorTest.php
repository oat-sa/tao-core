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

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Validator;

use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use oat\tao\helpers\form\elements\xhtml\SearchDropdown;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Validator\MetadataPropertiesValidator;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;
use tao_helpers_form_elements_Textbox;

class MetadataPropertiesValidatorTest extends TestCase
{
    /** @var MetadataPropertiesValidator */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new MetadataPropertiesValidator();
    }

    /**
     * @dataProvider metadataExistenceDataProvider
     */
    public function testValidateMetadataExistence(
        array $aliases,
        array $properties,
        string $exceptionClass = null
    ): void {
        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $this->assertNull($this->sut->validateMetadataExistence($aliases, $properties));
    }

    public function metadataExistenceDataProvider(): array
    {
        return [
            'Valid' => [
                'aliases' => ['alias'],
                'properties' => [$this->createProperty()],
            ],
            'Invalid - single metadata' => [
                'aliases' => ['alias', 'invalidAlias'],
                'properties' => [$this->createProperty()],
                'exception' => AggregatedValidationException::class,
            ],
            'Invalid - whole metadata' => [
                'aliases' => ['invalidAlias'],
                'properties' => [],
                'exception' => HeaderValidationException::class,
            ],
        ];
    }

    /**
     * @dataProvider metadataTypesDataProvider
     */
    public function testValidateMetadataTypes(
        array $properties,
        string $exceptionClass = null
    ): void {
        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $this->sut->validateMetadataTypes($properties);
    }

    public function metadataTypesDataProvider(): array
    {
        return [
            'Invalid - invalid widget' => [
                'properties' => [$this->createProperty('alias', true, SearchDropdown::WIDGET_ID)],
                'exception' => HeaderValidationException::class,
            ],
        ];
    }

    /**
     * @dataProvider metadataIsStatisticalDataProvider
     */
    public function testValidateMetadataIsStatistical(
        array $properties,
        string $exceptionClass = null
    ): void {
        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $this->sut->validateMetadataIsStatistical($properties);
    }

    public function metadataIsStatisticalDataProvider(): array
    {
        return [
            'Invalid - not statistical' => [
                'properties' => [$this->createProperty('alias', false)],
                'exception' => HeaderValidationException::class,
            ],
        ];
    }

    private function createProperty(
        string $alias = 'alias',
        bool $isStatistical = true,
        string $widgetUri = tao_helpers_form_elements_Textbox::WIDGET_ID
    ): core_kernel_classes_Property {
        $widget = $this->createMock(core_kernel_classes_Property::class);
        $widget->method('getUri')
            ->willReturn($widgetUri);

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->method('getAlias')
            ->willReturn($alias);

        $property->method('getLabel')
            ->willReturn($alias);

        $property->method('getWidget')
            ->willReturn($widget);

        $property->method('isStatistical')
            ->willReturn($isStatistical);

        return $property;
    }
}
