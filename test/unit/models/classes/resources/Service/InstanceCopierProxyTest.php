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
 * Copyright (c) 2022-2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\ResourceTransferInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;
use oat\tao\model\resources\Service\InstanceCopierProxy;
use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class InstanceCopierProxyTest extends TestCase
{
    /** @var Ontology|MockObject */
    private $ontology;
    private InstanceCopierProxy $sut;
    private ResourceTransferInterface $copier;
    private RootClassesListServiceInterface $rootClassesListService;
    private MockObject $rootClass;

    protected function setUp(): void
    {
        $this->rootClassesListService = $this->createMock(RootClassesListServiceInterface::class);
        $this->ontology = $this->createMock(Ontology::class);
        $this->copier = $this->createMock(ResourceTransferInterface::class);
        $this->rootClass = $this->createClass('rootClassUri');

        $this->rootClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('rootClassUri');

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$this->rootClass]);

        $this->sut = new InstanceCopierProxy($this->rootClassesListService, $this->ontology);
    }

    public function testTransfer(): void
    {
        $destinationClass = $this->createClass('destinationClassUri');

        $destinationClass->expects($this->once())
            ->method('equals')
            ->with($this->rootClass)
            ->willReturn(true);

        $destinationClass->expects($this->never())
            ->method('isSubClassOf')
            ->with($this->rootClass);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($destinationClass);

        $result = new ResourceTransferResult('newInstanceUri');
        $command = new ResourceTransferCommand(
            'instanceUri',
            'destinationClassUri',
            ResourceTransferCommand::ACL_KEEP_ORIGINAL,
            ResourceTransferCommand::TRANSFER_MODE_COPY
        );

        $this->sut->addInstanceCopier('rootClassUri', $this->copier);

        $this->copier
            ->expects($this->once())
            ->method('transfer')
            ->with($command)
            ->willReturn($result);

        $this->assertEquals($result, $this->sut->transfer($command));
    }

    public function testTransferWithNoCopier(): void
    {
        $destinationClass = $this->createClass('destinationClassUri');

        $destinationClass->expects($this->once())
            ->method('equals')
            ->with($this->rootClass)
            ->willReturn(true);

        $destinationClass->expects($this->never())
            ->method('isSubClassOf')
            ->with($this->rootClass);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($destinationClass);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no instance copier mapper for root class rootClassUri');

        $this->sut->transfer(
            new ResourceTransferCommand(
                'instanceUri',
                'destinationClassUri',
                ResourceTransferCommand::ACL_KEEP_ORIGINAL,
                ResourceTransferCommand::TRANSFER_MODE_COPY
            )
        );
    }

    /**
     * @return core_kernel_classes_Class|MockObject
     */
    private function createClass(string $uri): core_kernel_classes_Class
    {
        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->method('getUri')
            ->willReturn($uri);

        return $class;
    }
}
