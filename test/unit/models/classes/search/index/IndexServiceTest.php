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

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\SearchTokenGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class IndexServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const DOCUMENT_URI = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de';
    private const ARRAY_RESOURCE = [
        'id' => self::DOCUMENT_URI,
        'body' => [
            'type' => [],
        ]
    ];

    /** @var IndexService $sut */
    private $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var ServiceManager|MockObject */
    private $service;

    /** @var IndexDocumentBuilder|MockObject */
    private $indexDocumentBuilder;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->indexDocumentBuilder = $this->createMock(IndexDocumentBuilder::class);
        $this->sut = new IndexService();
        $this->sut->setOption(IndexService::OPTION_DOCUMENT_BUILDER, $this->indexDocumentBuilder);
        $this->sut->setServiceManager(
            $this->getServiceManagerMock(
                [
                    Ontology::SERVICE_ID => $this->ontology,
                    SearchTokenGenerator::class => new SearchTokenGenerator(),
                    LoggerService::SERVICE_ID => new NullLogger(),
                    PermissionInterface::SERVICE_ID => $this->createMock(PermissionInterface::class),
                ]
            )
        );
    }

    public function testCreateEmptyDocumentFromResource(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $document = new IndexDocument('id', ['type' => 'item']);

        $this->indexDocumentBuilder
            ->expects($this->once())
            ->method('createDocumentFromResource')
            ->willReturn($document);

        $this->assertSame($document, $this->sut->createDocumentFromResource($resource));
    }

    public function testCreateDocumentFromArray(): void
    {
        $document = new IndexDocument('id', ['type' => 'item']);

        $this->indexDocumentBuilder
            ->expects($this->once())
            ->method('createDocumentFromArray')
            ->willReturn($document);

        $this->assertSame($document, $this->sut->createDocumentFromArray(self::ARRAY_RESOURCE));
    }
}
