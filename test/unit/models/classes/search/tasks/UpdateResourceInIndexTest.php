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
 * Copyright (c) 2022-2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\search\tasks;

use common_exception_MissingParameter;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilderInterface;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\Search;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\tasks\UpdateResourceInIndex;
use oat\taoAdvancedSearch\model\Index\Service\AdvancedSearchIndexDocumentBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class UpdateResourceInIndexTest extends TestCase
{
    use ServiceManagerMockTrait;

    private UpdateResourceInIndex $sut;
    private IndexDocumentBuilderInterface|MockObject $documentBuilder;
    private Search $search;
    private core_kernel_classes_Resource|MockObject $resource1;
    private core_kernel_classes_Resource|MockObject $resource2;
    private IndexDocument|MockObject $document1;
    private IndexDocument|MockObject $document2;
    private AdvancedSearchChecker|MockObject $checker;

    protected function setUp(): void
    {
        $this->documentBuilder = $this->createMock(
            IndexDocumentBuilderInterface::class
        );
        $this->resource1 = $this->createMock(
            core_kernel_classes_Resource::class
        );
        $this->resource2 = $this->createMock(
            core_kernel_classes_Resource::class
        );

        $this->document1 = $this->createMock(IndexDocument::class);
        $this->document2 = $this->createMock(IndexDocument::class);

        $this->resource1
            ->method('getUri')
            ->willReturn('http://resource/1');
        $this->resource2
            ->method('getUri')
            ->willReturn('http://resource/2');

        $this->search = $this->createMock(SearchProxy::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->checker = $this->createMock(AdvancedSearchChecker::class);

        $ontology = $this->createMock(Ontology::class);
        $ontology
            ->method('getResource')
            ->willReturnCallback(function (string $uri) {
                switch ($uri) {
                    case 'http://resource/1':
                        return $this->resource1;
                    case 'http://resource/2':
                        return $this->resource2;
                }

                $this->fail("Unexpected resource requested: {$uri}");
            });

        $serviceLocator = $this->getServiceManagerMock(
            [
                Search::SERVICE_ID => $this->search,
                LoggerService::SERVICE_ID => $logger,
                Ontology::SERVICE_ID => $ontology,
                AdvancedSearchChecker::class => $this->checker,
                AdvancedSearchIndexDocumentBuilder::class => $this->documentBuilder
            ]
        );

        $this->sut = new UpdateResourceInIndex();
        $this->sut->setServiceLocator($serviceLocator);
    }

    public function testIndexDocuments(): void
    {
        $this->checker
            ->expects($this->exactly(1))
            ->method('isEnabled')
            ->willReturn(true);

        $this->documentBuilder
            ->expects($this->exactly(2))
            ->method('createDocumentFromResource')
            ->willReturnCallback(function (core_kernel_classes_Resource $r) {
                switch ($r->getUri()) {
                    case 'http://resource/1':
                        return $this->document1;
                    case 'http://resource/2':
                        return $this->document2;
                }

                $this->fail("Unexpected resource requested: {$r->getUri()}");
            });

        $this->search
            ->expects($this->once())
            ->method('index')
            ->with([
                $this->document1,
                $this->document2
            ]);

        $this->sut->__invoke([
            ['http://resource/1', 'http://resource/2'],
        ]);
    }

    /**
     * @dataProvider provideInvalidParameters
     */
    public function testInvalidParameters($parameters): void
    {
        $this->expectException(common_exception_MissingParameter::class);

        $this->sut->__invoke($parameters);
    }

    public function provideInvalidParameters(): array
    {
        return [
            'Empty Array' => [
                []
            ],
            'String' => [
                ''
            ],
            'Null' => [
                null
            ],
        ];
    }
}
