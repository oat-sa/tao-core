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
 * Copyright (c) 2021-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Lists\Business\Service;

use Exception;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\log\LoggerService;
use oat\tao\model\Lists\Business\Contract\ClassMetadataSearcherInterface;
use oat\tao\model\Lists\Business\Domain\ClassCollection;
use oat\tao\model\Lists\Business\Input\ClassMetadataSearchInput;
use oat\tao\model\Lists\Business\Service\ClassMetadataSearcherProxy;
use oat\tao\model\Lists\Business\Service\ClassMetadataService;
use PHPUnit\Framework\MockObject\MockObject;

class ClassMetadataSearcherProxyTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ClassMetadataSearcherProxy $subject;
    private ClassMetadataService|MockObject $classMetadataService;
    private ClassMetadataSearcherInterface|MockObject $searcher;
    private LoggerService|MockObject $logger;

    protected function setUp(): void
    {
        $this->subject = new ClassMetadataSearcherProxy(
            [
                ClassMetadataSearcherProxy::OPTION_ACTIVE_SEARCHER => ClassMetadataSearcherInterface::class,
            ]
        );

        $this->classMetadataService = $this->createMock(ClassMetadataService::class);
        $this->searcher = $this->createMock(ClassMetadataSearcherInterface::class);
        $this->logger = $this->createMock(LoggerService::class);

        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    ClassMetadataService::SERVICE_ID => $this->classMetadataService,
                    ClassMetadataSearcherInterface::class => $this->searcher,
                    LoggerService::SERVICE_ID => $this->logger
                ]
            )
        );
    }

    public function testWhenFindAllThrowsExceptionUseGenerisFallback(): void
    {
        $expectedCollection = new ClassCollection(...[]);

        $this->searcher
            ->method('findAll')
            ->willThrowException(new Exception('My exception'));

        $this->classMetadataService
            ->method('findAll')
            ->willReturn($expectedCollection);

        $this->logger
            ->method('critical');

        $result = $this->subject->findAll($this->createMock(ClassMetadataSearchInput::class));

        $this->assertSame($expectedCollection, $result);
    }

    public function testFindAll(): void
    {
        $expectedCollection = new ClassCollection(...[]);

        $this->searcher
            ->method('findAll')
            ->willReturn($expectedCollection);

        $result = $this->subject->findAll($this->createMock(ClassMetadataSearchInput::class));

        $this->assertSame($expectedCollection, $result);
    }
}
