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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 *
 */

declare(strict_types=1);

use oat\generis\model\data\Ontology;
use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\search\index\IndexDocument;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\search\index\DocumentBuilder\GenerisDocumentBuilderFactory;
use \oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilderInterface;
use \oat\tao\model\search\index\DocumentBuilder\DocumentBuilderFactoryInterface;

class GenerisIndexDocumentBuilderTest extends TestCase
{
    /** @var Ontology */
    private $ontology;

    /** @var ServiceManager|MockObject */
    private $service;
    
    /** @var DocumentBuilderFactoryInterface $factory */
    private $factory;
    
    private const ARRAY_RESOURCE = [
        'id' => 'https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de',
        'body' => [
            'type' => []
        ]
    ];
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->ontology = $this->createMock(Ontology::class);

        $this->service = $this->createMock(ServiceManager::class);

        $this->service->expects($this->any())
            ->method('get')
            ->with(Ontology::SERVICE_ID)
            ->willReturnCallback(
                function () {
                    $property = $this->createMock(core_kernel_classes_Property::class);
                    $property->expects($this->any())->method('getPropertyValues')->willReturn(
                        []
                    );

                    $ontology = $this->createMock(Ontology::class);
                    $ontology->expects($this->any())->method('getProperty')->willReturn(
                        $property
                    );
                    return $ontology;
                }
            );

        ServiceManager::setServiceManager($this->service);
        
        $this->factory = new GenerisDocumentBuilderFactory();
    }

    public function testCreateEmptyDocumentFromResource()
    {
        $resource = $this->createMock(
            core_kernel_classes_Resource::class
        );

        $resource->expects($this->any())->method('getTypes')->willReturn(
            []
        );
        $resource->expects($this->any())->method('getUri')->willReturn(
            'https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de'
        );
        
        $resourceTypes = $resource->getTypes();
        $resourceType = current(array_keys($resourceTypes)) ?: '';
    
        /** @var IndexDocumentBuilderInterface $documentBuilder */
        $documentBuilder = $this->factory->getDocumentBuilderByResourceType($resourceType);

        $document = $documentBuilder->createDocumentFromResource(
            $resource
        );

        $this->assertInstanceOf(IndexDocument::class, $document);

        $this->assertEquals('https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de', $document->getId());
        $this->assertEquals(['type'=>[]], $document->getBody());
        $this->assertEquals([], (array)$document->getDynamicProperties());
    }
    
    public function testCreateDocumentFromResource()
    {
        $resourceTypes = self::ARRAY_RESOURCE['body']['type'];
        $resourceType = current(array_keys($resourceTypes)) ?: '';
    
        /** @var IndexDocumentBuilderInterface $documentBuilder */
        $documentBuilder = $this->factory->getDocumentBuilderByResourceType($resourceType);
    
        $document = $documentBuilder->createDocumentFromArray(
            self::ARRAY_RESOURCE
        );
    
        $this->assertInstanceOf(IndexDocument::class, $document);
    
        $this->assertEquals('https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de', $document->getId());
        $this->assertEquals(['type'=>[]], $document->getBody());
        $this->assertEquals([], (array)$document->getDynamicProperties());
    }
}
