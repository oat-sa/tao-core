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

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Processor;

use core_kernel_classes_Resource;
use Exception;
use PHPUnit\Framework\TestCase;
use oat\tao\model\exceptions\UserErrorException;
use oat\tao\model\metadata\compiler\ResourceMetadataCompilerInterface;
use oat\tao\model\StatisticalMetadata\Import\Observer\ObserverFactory;
use oat\tao\model\StatisticalMetadata\Import\Processor\NotifyImportService;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use SplObserver;

class NotifyImportServiceTest extends TestCase
{
    /** @var NotifyImportService */
    private $sut;

    /** @var ResourceMetadataCompilerInterface|MockObject */
    private $resourceMetadataCompiler;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var ObserverFactory|MockObject */
    private $observerFactory;

    /** @var MockObject|SplObserver */
    private $observer;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resourceMetadataCompiler = $this->createMock(ResourceMetadataCompilerInterface::class);
        $this->observerFactory = $this->createMock(ObserverFactory::class);
        $this->observer = $this->createMock(SplObserver::class);

        $this->sut = new NotifyImportService(
            $this->logger,
            $this->resourceMetadataCompiler,
            $this->observerFactory
        );
    }

    public function testNotifyWithSuccess(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $this->resourceMetadataCompiler
            ->expects($this->once())
            ->method('compile')
            ->with($resource)
            ->willReturn(['compiled']);

        $this->observerFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->observer);

        $this->observer
            ->expects($this->once())
            ->method('update');

        $this->sut
            ->addResource($resource)
            ->notify();

        $resources = new ReflectionProperty($this->sut, 'resources');
        $resources->setAccessible(true);

        $this->assertCount(0, $resources->getValue($this->sut));
    }

    public function testCannotAddSameResourceTwice(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);
        $resource->expects($this->exactly(3))
            ->method('getUri')
            ->willReturn('uri');

        $this->sut->addResource($resource);
        $this->sut->addResource($resource);
        $this->sut->addResource($resource);

        $resources = new ReflectionProperty($this->sut, 'resources');
        $resources->setAccessible(true);

        $this->assertCount(1, $resources->getValue($this->sut));
    }

    public function testIfNotifyExceedsRetriesThrowsException(): void
    {
        $numberOfTries = 5;
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $this->resourceMetadataCompiler
            ->expects($this->once())
            ->method('compile')
            ->with($resource)
            ->willReturn(['compiled']);

        $this->observerFactory
            ->expects($this->exactly($numberOfTries))
            ->method('create')
            ->willThrowException(new Exception('Some error'));

        $this->logger
            ->expects($this->exactly($numberOfTries + 1))
            ->method('error');

        $this->observer
            ->expects($this->never())
            ->method('update');

        $this->expectException(UserErrorException::class);

        $this->sut
            ->withMaxTries($numberOfTries)
            ->withRetryDelay(0)
            ->addResource($resource)
            ->notify();
    }
}
