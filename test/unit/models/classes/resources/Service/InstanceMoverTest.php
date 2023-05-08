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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use core_kernel_classes_Class;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\PermissionCopierInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;
use oat\tao\model\resources\Service\InstanceMover;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstanceMoverTest extends TestCase
{
    private InstanceMover $sut;

    /** @var MockObject|Ontology */
    private Ontology $ontology;

    /** @var MockObject|RootClassesListServiceInterface */
    private RootClassesListServiceInterface $rootClassesListService;

    /** @var MockObject|PermissionCopierInterface */
    private PermissionCopierInterface $permissionCopier;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->rootClassesListService = $this->createMock(RootClassesListServiceInterface::class);
        $this->permissionCopier = $this->createMock(PermissionCopierInterface::class);

        $this->sut = new InstanceMover($this->ontology, $this->rootClassesListService);
    }

    /**
     * @dataProvider transferDataProvider
     */
    public function testTransfer(bool $issetPermissionCopier, bool $useDestinationAcl): void
    {
        $resourceTransferCommand = $this->createMock(ResourceTransferCommand::class);
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getFrom')
            ->willReturn('fromInstanceUri');
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getTo')
            ->willReturn('toClassUri');

        $fromInstance = $this->createMock(core_kernel_classes_Class::class);
        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('fromInstanceUri')
            ->willReturn($fromInstance);
        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with('toClassUri')
            ->willReturn($toClass);

        $fromClass = $this->createMock(core_kernel_classes_Class::class);

        $fromInstance
            ->expects($this->once())
            ->method('getTypes')
            ->willReturn([$fromClass]);

        $rootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$rootClass]);

        $fromClass
            ->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(false);
        $fromClass
            ->expects($this->once())
            ->method('isSubClassOf')
            ->with($rootClass)
            ->willReturn(true);

        $toClass
            ->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(false);
        $toClass
            ->expects($this->once())
            ->method('isSubClassOf')
            ->with($rootClass)
            ->willReturn(true);

        $fromInstance
            ->expects($this->once())
            ->method('removeType')
            ->with($fromClass);
        $fromInstance
            ->expects($this->once())
            ->method('setType')
            ->with($toClass);

        if ($issetPermissionCopier) {
            $this->sut->withPermissionCopier($this->permissionCopier);
        }

        $resourceTransferCommand
            ->expects($issetPermissionCopier ? $this->once() : $this->never())
            ->method('useDestinationAcl')
            ->willReturn($useDestinationAcl);

        $this->permissionCopier
            ->expects($issetPermissionCopier && $useDestinationAcl ? $this->once() : $this->never())
            ->method('copy')
            ->with($toClass, $fromInstance);

        $fromInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fromInstanceUri');

        $this->assertEquals(
            new ResourceTransferResult('fromInstanceUri'),
            $this->sut->transfer($resourceTransferCommand)
        );
    }

    public function testTransferAssertIsInSameRootClass(): void
    {
        $resourceTransferCommand = $this->createMock(ResourceTransferCommand::class);
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getFrom')
            ->willReturn('fromInstanceUri');
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getTo')
            ->willReturn('toClassUri');

        $fromInstance = $this->createMock(core_kernel_classes_Class::class);
        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('fromInstanceUri')
            ->willReturn($fromInstance);
        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->with('toClassUri')
            ->willReturn($toClass);

        $fromClass = $this->createMock(core_kernel_classes_Class::class);

        $fromInstance
            ->expects($this->once())
            ->method('getTypes')
            ->willReturn([$fromClass]);

        $rootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$rootClass]);

        $fromClass
            ->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(false);
        $fromClass
            ->expects($this->once())
            ->method('isSubClassOf')
            ->with($rootClass)
            ->willReturn(true);

        $toClass
            ->expects($this->once())
            ->method('equals')
            ->with($rootClass)
            ->willReturn(false);
        $toClass
            ->expects($this->once())
            ->method('isSubClassOf')
            ->with($rootClass)
            ->willReturn(false);

        $fromInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fromInstanceUri');

        $toClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('toClassUri');

        $rootClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('rootClassUri');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Selected instance (fromInstanceUri) and destination class (toClassUri) must be in the same root class '
            . '(rootClassUri).'
        );

        $this->sut->transfer($resourceTransferCommand);
    }

    public function transferDataProvider(): array
    {
        return [
            'Test transfer without permission copier and original ACL' => [
                'issetPermissionCopier' => false,
                'useDestinationAcl' => false,
            ],
            'Test transfer without permission copier and destination ACL' => [
                'issetPermissionCopier' => false,
                'useDestinationAcl' => true,
            ],
            'Test transfer with permission copier and original ACL' => [
                'issetPermissionCopier' => true,
                'useDestinationAcl' => false,
            ],
            'Test transfer with permission copier and destination ACL' => [
                'issetPermissionCopier' => true,
                'useDestinationAcl' => true,
            ],
        ];
    }
}
