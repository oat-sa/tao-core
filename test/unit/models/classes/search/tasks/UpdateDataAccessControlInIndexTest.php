<?php

/** @noinspection PhpUndefinedClassInspection */

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
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\search\tasks;

use common_exception_MissingParameter;
use common_report_Report as Report;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use Exception;
use oat\generis\model\data\Ontology;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\search\index\IndexUpdaterInterface;
use oat\tao\model\search\tasks\UpdateDataAccessControlInIndex;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class UpdateDataAccessControlInIndexTest extends TestCase
{
    use ServiceManagerMockTrait;

    private UpdateDataAccessControlInIndex $sut;
    private IndexUpdaterInterface|MockObject $indexUpdater;
    private LoggerInterface $logger;
    private Ontology|MockObject $ontology;

    protected function setUp(): void
    {
        $this->sut = new UpdateDataAccessControlInIndex();
        $this->indexUpdater = $this->createMock(IndexUpdaterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->ontology = $this->createMock(Ontology::class);

        $serviceLocator = $this->getServiceManagerMock(
            [
                IndexUpdaterInterface::SERVICE_ID => $this->indexUpdater,
                LoggerService::SERVICE_ID => $this->logger,
                Ontology::SERVICE_ID => $this->ontology,
            ]
        );

        $this->sut->setServiceLocator($serviceLocator);
    }

    public function testInvokeWithWrongParametersShouldThrowException(): void
    {
        $this->expectException(common_exception_MissingParameter::class);

        $this->logger->expects($this->never())->method('info');

        $this->indexUpdater->expects($this->never())->method('updatePropertyValue');

        $this->sut->__invoke(
            []
        );
    }

    public function testInvokeTaskFailureShouldReportError(): void
    {
        $documentUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->expects($this->once())->method('getParentClasses')->with(true)->willReturn([]);
        $resource->expects($this->once())->method('isClass')->willReturn(false);
        $resource->expects($this->once())->method('getTypes')->willReturn([$class]);

        $this->ontology->expects($this->once())->method('getResource')
            ->willReturn($resource);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Data Access Control failure: (Exception) fail');

        $this->indexUpdater->expects($this->once())->method('updatePropertyValue')
            ->with($documentUri, [''], 'read_access', [])
            ->willThrowException(new Exception('fail'));

        $report = $this->sut->__invoke(
            [$documentUri, []]
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('Failed during update search index', $report->getMessage());
        $this->assertEquals(Report::TYPE_ERROR, $report->getType());
    }

    public function testInvokeTaskSuccessfullyInCaseIsResource(): void
    {
        $documentUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708e84ec';
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->expects($this->once())->method('getParentClasses')->with(true)->willReturn([]);
        $resource->expects($this->once())->method('isClass')->willReturn(false);
        $resource->expects($this->once())->method('getTypes')->willReturn([$class]);

        $this->ontology->expects($this->once())->method('getResource')
            ->willReturn($resource);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                'Data Access Control were being updated by oat\tao\model\search\tasks\UpdateDataAccessControlInIndex'
            );

        $this->indexUpdater->expects($this->once())->method('updatePropertyValue')
            ->with($documentUri, [''], 'read_access', []);

        $report = $this->sut->__invoke(
            [$documentUri, []]
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('Documents in index were successfully updated.', $report->getMessage());
        $this->assertEquals(Report::TYPE_SUCCESS, $report->getType());
    }

    public function testInvokeTaskSuccessfullyInCaseResourceIsClass(): void
    {
        $documentUri = 'https://tao.docker.localhost/ontologies/tao.rdf#i5ef45f413088c8e7901a84708as123';
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $class = $this->createMock(core_kernel_classes_Class::class);
        $parentClass = $this->createMock(core_kernel_classes_Class::class);

        $parentClass->expects($this->once())->method('getUri')
            ->willReturn('https://tao.docker.localhost/ontologies/tao.rdf#Item');

        $class->expects($this->once())->method('getParentClasses')->with(true)
            ->willReturn([$parentClass]);

        $resource->expects($this->once())->method('isClass')->willReturn(true);

        $this->ontology->expects($this->once())->method('getResource')
            ->willReturn($resource);
        $this->ontology->expects($this->once())->method('getClass')
            ->willReturn($class);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                'Data Access Control were being updated by oat\tao\model\search\tasks\UpdateDataAccessControlInIndex'
            );

        $this->indexUpdater->expects($this->once())->method('updatePropertyValue')
            ->with($documentUri, ['https://tao.docker.localhost/ontologies/tao.rdf#Item'], 'read_access', []);

        $report = $this->sut->__invoke(
            [$documentUri, []]
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('Documents in index were successfully updated.', $report->getMessage());
        $this->assertEquals(Report::TYPE_SUCCESS, $report->getType());
    }
}
