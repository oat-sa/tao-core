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

use oat\generis\test\TestCase;
use core_kernel_classes_Property;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Validator\MetadataPropertiesValidator;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;

class MetadataPropertiesValidatorTest extends TestCase
{
    /** @var MetadataPropertiesValidator */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new MetadataPropertiesValidator();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidateMetadataExistence(
        array $aliases,
        array $properties,
        string $exceptionClass = null
    ): void {
        if ($exceptionClass) {
            $this->expectException($exceptionClass);
        }

        $this->sut->validateMetadataExistence($aliases, $properties);
    }

    public function dataProvider(): array
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

    private function createProperty(): core_kernel_classes_Property
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property
            ->expects($this->once())
            ->method('getAlias')
            ->willReturn('alias');

        return $property;
    }
}
