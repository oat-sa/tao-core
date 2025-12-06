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
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use PHPUnit\Framework\MockObject\MockObject;
use core_kernel_classes_ContainerCollection;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;
use oat\tao\model\StatisticalMetadata\Import\Validator\ResourceMetadataRelationValidator;

class ResourceMetadataRelationValidatorTest extends TestCase
{
    /** @var core_kernel_classes_Resource|MockObject */
    private $resource;

    /** @var core_kernel_classes_Property|MockObject */
    private $metadataProperty;

    /** @var ResourceMetadataRelationValidator */
    private $sut;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->metadataProperty = $this->createMock(core_kernel_classes_Property::class);

        $this->sut = new ResourceMetadataRelationValidator();
    }

    public function testValidateValid(): void
    {
        $domain = $this->createMock(core_kernel_classes_Class::class);

        $collection = $this->createMock(core_kernel_classes_ContainerCollection::class);
        $collection
            ->expects($this->once())
            ->method('get')
            ->with(0)
            ->willReturn($domain);

        $this->metadataProperty
            ->expects($this->exactly(2))
            ->method('getDomain')
            ->willReturn($collection);

        $this->resource
            ->expects($this->once())
            ->method('isInstanceOf')
            ->with($domain)
            ->willReturn(true);
        $this->resource
            ->expects($this->never())
            ->method('getUri');

        $this->metadataProperty
            ->expects($this->never())
            ->method('getAlias');

        $this->sut->validate($this->resource, $this->metadataProperty);
    }

    public function testValidateInvalidNoDomain(): void
    {
        $this->metadataProperty
            ->expects($this->once())
            ->method('getDomain')
            ->willReturn(null);

        $this->resource
            ->expects($this->never())
            ->method('isInstanceOf');
        $this->resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('resourceUri');

        $this->metadataProperty
            ->expects($this->once())
            ->method('getAlias')
            ->willReturn('alias');

        $this->expectException(ErrorValidationException::class);

        $this->sut->validate($this->resource, $this->metadataProperty);
    }

    public function testValidateInvalidWrongDomain(): void
    {
        $domain = $this->createMock(core_kernel_classes_Class::class);

        $collection = $this->createMock(core_kernel_classes_ContainerCollection::class);
        $collection
            ->expects($this->once())
            ->method('get')
            ->with(0)
            ->willReturn($domain);

        $this->metadataProperty
            ->expects($this->exactly(2))
            ->method('getDomain')
            ->willReturn($collection);

        $this->resource
            ->expects($this->once())
            ->method('isInstanceOf')
            ->with($domain)
            ->willReturn(false);
        $this->resource
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('resourceUri');

        $this->metadataProperty
            ->expects($this->once())
            ->method('getAlias')
            ->willReturn('alias');

        $this->expectException(ErrorValidationException::class);

        $this->sut->validate($this->resource, $this->metadataProperty);
    }
}
