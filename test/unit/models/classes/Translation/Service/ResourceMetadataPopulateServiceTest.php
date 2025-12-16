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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Translation\Service;

use core_kernel_classes_Literal;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdf;
use oat\tao\model\Translation\Entity\AbstractResource;
use oat\tao\model\Translation\Service\ResourceMetadataPopulateService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResourceMetadataPopulateServiceTest extends TestCase
{
    private ResourceMetadataPopulateService $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var AbstractResource|MockObject */
    private $resource;

    /** @var core_kernel_classes_Resource|MockObject */
    private $originResource;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->resource = $this->createMock(AbstractResource::class);
        $this->originResource = $this->createMock(core_kernel_classes_Resource::class);
        $this->sut = new ResourceMetadataPopulateService($this->ontology);
    }

    public function testPopulateAddsMetadataToResource(): void
    {
        $resourceType = 'testResourceType';
        $metadataUri = 'http://example.com/metadata';
        $valuePropertyUri = OntologyRdf::RDF_VALUE;
        $value = 'testValue';
        $literalValue = 'testLiteral';
        $valueProperty = $this->createMock(core_kernel_classes_Property::class);
        $literal = $this->createMock(core_kernel_classes_Literal::class);
        $literal->literal = $literalValue;

        $this->sut->addMetadata($resourceType, $metadataUri);

        $this->originResource
            ->method('getParentClassesIds')
            ->willReturn([$resourceType]);

        $this->originResource
            ->method('getPropertyValues')
            ->willReturn([$value]);

        $this->resource
            ->expects($this->once())
            ->method('addMetadataUri')
            ->with($metadataUri);

        $this->ontology->method('getProperty')->willReturnMap(
            [
                [
                    $metadataUri,
                    $valueProperty
                ],
                [
                    $value,
                    $valueProperty
                ],
                [
                    $valuePropertyUri,
                    $valueProperty
                ],
            ]
        );

        $valueProperty
            ->method('getOnePropertyValue')
            ->willReturn($literal);

        $this->resource
            ->expects($this->once())
            ->method('addMetadata')
            ->with($metadataUri, $value, $literalValue);

        $this->resource
            ->expects($this->once())
            ->method('getMetadataUris')
            ->willReturn([$metadataUri]);

        $this->sut->populate($this->resource, $this->originResource);
    }
}
