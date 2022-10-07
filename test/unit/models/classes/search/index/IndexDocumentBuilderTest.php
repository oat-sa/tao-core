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
use oat\generis\model\data\Ontology;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\ServiceManagerMockTrait;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\DocumentBuilder\PropertyIndexReferenceFactory;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\SearchTokenGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class IndexDocumentBuilderTest extends TestCase
{
    use OntologyMockTrait;
    use ServiceManagerMockTrait;

    /** @var ServiceManager|MockObject */
    private $serviceLocatorMock;

    /** @var IndexDocumentBuilder $builder */
    private $builder;

    private const RESOURCE_URI = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de';

    private const ARRAY_RESOURCE = [
        'id' => self::RESOURCE_URI,
        'body' => [
            'type' => []
        ]
    ];

    public function setUp(): void
    {
        $this->propertyIndexReferenceFactory = $this->createMock(PropertyIndexReferenceFactory::class);
        $this->builder = new IndexDocumentBuilder();
        $this->builder->setServiceManager(
            $this->getServiceManagerMock(
                [
                    Ontology::SERVICE_ID => $this->getOntologyMock(),
                    PermissionInterface::SERVICE_ID => $this->createMock(PermissionInterface::class),
                    SearchTokenGenerator::class => new SearchTokenGenerator(),
                    LoggerService::SERVICE_ID => new NullLogger(),
                    PropertyIndexReferenceFactory::class => $this->propertyIndexReferenceFactory,
                ]
            )
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
            new ArrayIterator()
        );

        $this->assertEquals($document, $this->builder->createDocumentFromResource($resource));
    }

    public function testCreateDocumentFromArray(): void
    {
        $this->assertEquals(
            new IndexDocument(self::ARRAY_RESOURCE['id'], self::ARRAY_RESOURCE['body']),
            $this->builder->createDocumentFromArray(self::ARRAY_RESOURCE)
        );
    }
}
