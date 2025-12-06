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

use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Resource;
use oat\tao\model\StatisticalMetadata\Import\Validator\RecordResourceValidator;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;

class RecordResourceValidatorTest extends TestCase
{
    /** @var RecordResourceValidator */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new RecordResourceValidator();
    }

    /**
     * @dataProvider dataProviderValidateResourceIdValid
     */
    public function testValidateResourceIdValid(array $record): void
    {
        $this->expectNotToPerformAssertions();

        $this->sut->validateResourceId($record);
    }

    /**
     * @dataProvider dataProviderValidateResourceIdInvalid
     */
    public function testValidateResourceIdInvalid(array $record): void
    {
        $this->expectException(ErrorValidationException::class);

        $this->sut->validateResourceId($record);
    }

    public function testValidateResourceAvailabilityValid(): void
    {
        $resource = $this->createResource(['exists' => true]);

        $this->sut->validateResourceAvailability($resource);
    }

    public function testValidateResourceAvailabilityInvalid(): void
    {
        $resource = $this->createResource(
            [
                'exists' => false,
                'uri' => 'resourceUri',
            ]
        );

        $this->expectException(ErrorValidationException::class);

        $this->sut->validateResourceAvailability($resource);
    }

    public function testValidateResourceTypeValid(): void
    {
        $rootClass = $this->createMock(core_kernel_classes_Class::class);
        $resource = $this->createResource(
            [
                'isInstanceOf' => true,
                'rootClass' => $rootClass,
            ]
        );

        $this->sut->validateResourceType($resource, $rootClass);
    }

    public function testValidateResourceTypeInvalid(): void
    {
        $rootClass = $this->createMock(core_kernel_classes_Class::class);
        $resource = $this->createResource(
            [
                'isInstanceOf' => false,
                'rootClass' => $rootClass,
                'uri' => 'resourceUri',
            ]
        );

        $this->expectException(ErrorValidationException::class);

        $this->sut->validateResourceType($resource, $rootClass);
    }

    public function dataProviderValidateResourceIdValid(): array
    {
        return [
            'Valid - itemId' => [
                'record' => [
                    'itemId' => 'id',
                    'testId' => '',
                ],
            ],
            'Valid - testId' => [
                'record' => [
                    'itemId' => '',
                    'testId' => 'id',
                ],
            ],
            'Valid - both' => [
                'record' => [
                    'itemId' => 'id',
                    'testId' => 'id',
                ],
            ],
        ];
    }

    public function dataProviderValidateResourceIdInvalid(): array
    {
        return [
            'Invalid - empty values' => [
                'record' => [
                    'itemId' => '',
                    'testId' => '',
                ],
            ],
            'Invalid - only headers' => [
                'record' => [
                    'itemId',
                    'testId',
                ],
            ],
        ];
    }

    private function createResource(array $data): core_kernel_classes_Resource
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        if (isset($data['exists'])) {
            $resource
                ->expects($this->once())
                ->method('exists')
                ->willReturn($data['exists']);
        }

        if (isset($data['isInstanceOf']) && isset($data['rootClass'])) {
            $resource
                ->expects($this->once())
                ->method('isInstanceOf')
                ->with($data['rootClass'])
                ->willReturn($data['isInstanceOf']);
        }

        $resource
            ->expects(isset($data['uri']) ? $this->once() : $this->never())
            ->method('getUri')
            ->willReturn($data['uri'] ?? '');

        return $resource;
    }
}
