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
 * Copyright (c) 2020-2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\search\index;

use oat\generis\model\data\Ontology;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\test\OntologyMockTrait;
use oat\generis\test\TestCase;
use oat\oatbox\log\LoggerService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\index\IndexService;
use oat\tao\model\search\SearchTokenGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;

class IndexServiceTest extends TestCase
{
    use OntologyMockTrait;

    private const DOCUMENT_URI = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ecbaaf0a627c73a7996557a5480de';

    /** @var Ontology */
    private $ontology;

    /** @var ServiceManager|MockObject */
    private $service;

    /** @var IndexService $indexService */
    private $indexService;

    private const ARRAY_RESOURCE = [
        'id' => self::DOCUMENT_URI,
        'body' => [
            'type' => [],
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->ontology = $this->getOntologyMock();

        $this->service = $this->getServiceLocatorMock(
            [
                Ontology::SERVICE_ID => $this->ontology,
                SearchTokenGenerator::class => new SearchTokenGenerator(),
                LoggerService::SERVICE_ID => new NullLogger(),
                PermissionInterface::SERVICE_ID => $this->createMock(PermissionInterface::class),
            ]
        );
    }

    private function getIndexService(): IndexService
    {
        if (!$this->indexService) {
            $this->indexService = new IndexService();
            $this->indexService->setOption(IndexService::OPTION_DOCUMENT_BUILDER, (new IndexDocumentBuilder()));
            $this->indexService->setServiceLocator($this->service);
        }

        return $this->indexService;
    }

    public function testCreateEmptyDocumentFromResource(): void
    {
        $indexService = $this->getIndexService();

        $resource = $this->ontology->getClass(self::DOCUMENT_URI);

        $document = $indexService->createDocumentFromResource(
            $resource
        );

        $this->assertInstanceOf(IndexDocument::class, $document);

        $this->assertEquals(self::DOCUMENT_URI, $document->getId());
        $this->assertEquals(['type' => [], 'parent_classes' => ''], $document->getBody());
        $this->assertEquals([], (array)$document->getDynamicProperties());
    }

    public function testCreateDocumentFromResource(): void
    {
        $indexService = $this->getIndexService();

        $document = $indexService->createDocumentFromArray(
            self::ARRAY_RESOURCE
        );

        $this->assertInstanceOf(IndexDocument::class, $document);

        $this->assertEquals(self::DOCUMENT_URI, $document->getId());
        $this->assertEquals(['type' => []], $document->getBody());
        $this->assertEquals([], (array)$document->getDynamicProperties());
    }

}
