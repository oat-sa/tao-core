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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search\index;

use ArrayIterator;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\data\permission\ReverseRightLookupInterface;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\DocumentBuilder\PropertyIndexReferenceFactory;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class IndexDocumentBuilderTest extends TestCase
{
    use OntologyMockTrait;
    use ServiceManagerMockTrait;

    private const RESOURCE_URI = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de';

    private const ARRAY_RESOURCE = [
        'id' => self::RESOURCE_URI,
        'body' => [
            'type' => []
        ]
    ];

    private ServiceManager|MockObject $serviceLocatorMock;
    private IndexDocumentBuilder $builder;
    private MockObject|string $ontologyMock;
    private core_kernel_classes_Resource|MockObject $resourceMock;
    private SearchTokenGenerator|MockObject $tokenGeneratorMock;
    private PermissionInterface|MockObject $permissionProvider;

    protected function setUp(): void
    {
        $this->ontologyMock = $this->createMock(Ontology::class);
        $this->resourceMock = $this->createMock(
            core_kernel_classes_Resource::class
        );
        $propertyIndexReferenceFactory = $this->createMock(
            PropertyIndexReferenceFactory::class
        );

        $this->ontologyMock
            ->method('getResource')
            ->with(self::RESOURCE_URI)
            ->willReturn($this->resourceMock);

        $this->tokenGeneratorMock = $this->createMock(
            SearchTokenGenerator::class
        );
        $this->permissionProvider = $this->createMock(
            ReverseRightLookupInterface::class
        );


        $this->builder = new IndexDocumentBuilder(
            $this->ontologyMock,
            $this->tokenGeneratorMock,
            $propertyIndexReferenceFactory,
            $this->createMock(ValueCollectionService::class),
            $this->createMock(RemoteListPropertySpecification::class),
            $this->permissionProvider
        );
    }

    public function testCreateEmptyDocumentFromResource(): void
    {
        $resource = $this->getOntologyMock()->getResource(self::RESOURCE_URI);
        $document = new IndexDocument(
            self::ARRAY_RESOURCE['id'],
            [
                'type' => [],
                'parent_classes' => '',
                'location' => '',
                'updated_at' => '',
            ],
            [],
            new ArrayIterator(),
            new ArrayIterator(['read_access' => []])
        );

        $this->permissionProvider
            ->expects($this->once())
            ->method('getResourceAccessData')
            ->willReturn([]);

        $this->assertEquals(
            $document,
            $this->builder->createDocumentFromResource($resource)
        );
    }

    public function testCreateDocumentFromArray(): void
    {
        $updatedAtMock = $this->createMock(core_kernel_classes_Property::class);
        $updatedAtMock
            ->method('__toString')
            ->willReturn('Updated At');

        $this->resourceMock
            ->method('getUri')
            ->willReturn(self::RESOURCE_URI);
        $this->resourceMock
            ->method('getTypes')
            ->willReturn([]);
        $this->resourceMock
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_UPDATED_AT)
            ->willReturn($updatedAtMock);

        $this->ontologyMock
            ->method('getResource')
            ->with(self::RESOURCE_URI)
            ->willReturn($this->resourceMock);

        $this->tokenGeneratorMock
            ->expects($this->once())
            ->method('generateTokens')
            ->willReturnCallback(function (core_kernel_classes_Resource $resource) {
                if ($resource->getUri() === self::RESOURCE_URI) {
                    return [];
                }

                $this->fail(
                    "Unexpected resource requested: {$resource->getUri()}"
                );
            });

        $this->permissionProvider
            ->expects($this->once())
            ->method('getResourceAccessData')
            ->willReturn([]);

        $doc = $this->builder->createDocumentFromArray(self::ARRAY_RESOURCE);

        $this->assertEquals(self::ARRAY_RESOURCE['id'], $doc->getId());
        $this->assertEquals(self::ARRAY_RESOURCE['body'], $doc->getBody());
        $this->assertEquals([], $doc->getIndexProperties());
        $this->assertEquals([], iterator_to_array($doc->getDynamicProperties()));
    }

    /**
     * @dataProvider createDocumentFromArrayThrowsOnInvalidInputDataProvider
     */
    public function testCreateDocumentFromArrayThrowsOnInvalidInput(array $resourceData): void
    {
        $this->expectException(\common_exception_MissingParameter::class);

        $this->permissionProvider
            ->expects($this->never())
            ->method('getResourceAccessData');

        $updatedAtMock = $this->createMock(core_kernel_classes_Property::class);
        $updatedAtMock
            ->expects($this->never())
            ->method('__toString');

        $this->resourceMock
            ->expects($this->never())
            ->method('getUri');

        $this->resourceMock
            ->expects($this->never())
            ->method('getTypes');

        $this->resourceMock
            ->expects($this->never())
            ->method('getProperty');

        $this->ontologyMock
            ->expects($this->never())
            ->method('getResource');

        $this->tokenGeneratorMock
            ->expects($this->never())
            ->method('generateTokens');

        $this->builder->createDocumentFromArray($resourceData);
    }

    public function createDocumentFromArrayThrowsOnInvalidInputDataProvider(): array
    {
        return [
            'Missing ID property' => [
                [
                    'body' => [
                        'type' => []
                    ]
                ]
            ],
            'Missing body property' => [
                [
                    'id' => self::RESOURCE_URI,
                ]
            ],
            'Missing both ID and body' => [
                []
            ],
        ];
    }

    public function testCreateDocumentFromArrayUsesProvidedIndexProperties(): void
    {
        $this->permissionProvider
            ->expects($this->once())
            ->method('getResourceAccessData')
            ->willReturn([]);

        $updatedAtMock = $this->createMock(core_kernel_classes_Property::class);
        $updatedAtMock
            ->method('__toString')
            ->willReturn('Updated At');

        $this->resourceMock
            ->method('getUri')
            ->willReturn(self::RESOURCE_URI);
        $this->resourceMock
            ->method('getTypes')
            ->willReturn([]);
        $this->resourceMock
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_UPDATED_AT)
            ->willReturn($updatedAtMock);

        $this->ontologyMock
            ->method('getResource')
            ->with(self::RESOURCE_URI)
            ->willReturn($this->resourceMock);

        $this->tokenGeneratorMock
            ->expects($this->never())
            ->method('generateTokens');

        $this->permissionProvider
            ->expects($this->once())
            ->method('getResourceAccessData')
            ->willReturn([]);

        $data = [
            'id' => self::RESOURCE_URI,
            'body' => [
                'type' => ['type1']
            ],
            'indexProperties' => [
                'property' => 'value',
            ],
        ];

        $doc = $this->builder->createDocumentFromArray($data);

        $this->assertEquals(self::RESOURCE_URI, $doc->getId());
        $this->assertEquals(['type' => ['type1']], $doc->getBody());
        $this->assertEquals(['property' => 'value'], $doc->getIndexProperties());
        $this->assertEquals([], iterator_to_array($doc->getDynamicProperties()));
    }

    public function testCreateDocumentFromArrayUsesPermissionsFromProvider(): void
    {
        $updatedAtMock = $this->createMock(core_kernel_classes_Property::class);
        $updatedAtMock
            ->method('__toString')
            ->willReturn('Updated At');

        $this->resourceMock
            ->method('getUri')
            ->willReturn(self::RESOURCE_URI);
        $this->resourceMock
            ->method('getTypes')
            ->willReturn([]);
        $this->resourceMock
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_UPDATED_AT)
            ->willReturn($updatedAtMock);

        $this->ontologyMock
            ->method('getResource')
            ->with(self::RESOURCE_URI)
            ->willReturn($this->resourceMock);

        $this->tokenGeneratorMock
            ->expects($this->never())
            ->method('generateTokens');

        $data = [
            'id' => self::RESOURCE_URI,
            'body' => [
                'type' => ['type1']
            ],
            'indexProperties' => [
                'property' => 'value',
            ],
        ];

        $this->permissionProvider
            ->expects($this->once())
            ->method('getResourceAccessData')
            ->willReturn([
                'right1' => 'value',
                'right2' => 'value',
            ]);

        $doc = $this->builder->createDocumentFromArray($data);

        $this->assertEquals(self::RESOURCE_URI, $doc->getId());
        $this->assertEquals(['type' => ['type1']], $doc->getBody());
        $this->assertEquals(['property' => 'value'], $doc->getIndexProperties());
        $this->assertEquals([], iterator_to_array($doc->getDynamicProperties()));
    }
}
