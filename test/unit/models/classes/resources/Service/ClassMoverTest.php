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
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\ClassMovedEvent;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Contract\PermissionCopierInterface;
use oat\tao\model\resources\Contract\RootClassesListServiceInterface;
use oat\tao\model\resources\ResourceTransferResult;
use oat\tao\model\resources\Service\ClassMover;
use oat\tao\model\Specification\ClassSpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClassMoverTest extends TestCase
{
    private ClassMover $sut;

    /** @var MockObject|Ontology */
    private MockObject $ontology;

    /** @var MockObject|ClassSpecificationInterface */
    private MockObject $rootClassSpecification;

    /** @var MockObject|RootClassesListServiceInterface */
    private MockObject $rootClassesListService;

    /** @var MockObject|EventManager */
    private MockObject $eventManager;

    /** @var MockObject|PermissionCopierInterface */
    private MockObject $permissionCopier;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->rootClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->rootClassesListService = $this->createMock(RootClassesListServiceInterface::class);
        $this->eventManager = $this->createMock(EventManager::class);
        $this->permissionCopier = $this->createMock(PermissionCopierInterface::class);

        $this->sut = new ClassMover(
            $this->ontology,
            $this->rootClassSpecification,
            $this->rootClassesListService,
            $this->eventManager
        );
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
            ->willReturn('fromClassUri');
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getTo')
            ->willReturn('toClassUri');

        $fromClass = $this->createMock(core_kernel_classes_Class::class);
        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getClass')
            ->willReturnCallback(
                fn (string $uri): core_kernel_classes_Class => $uri === 'fromClassUri' ? $fromClass : $toClass
            );

        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->with($fromClass)
            ->willReturn(false);

        $rootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$rootClass]);

        $fromClass
            ->expects($this->exactly(2))
            ->method('equals')
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
            ->expects($this->exactly(2))
            ->method('isSubClassOf')
            ->willReturnCallback(fn (core_kernel_classes_Class $class): bool => $class === $rootClass);

        $subclassOfProperty = $this->createMock(core_kernel_classes_Property::class);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(OntologyRdfs::RDFS_SUBCLASSOF)
            ->willReturn($subclassOfProperty);

        $fromClass
            ->expects($this->once())
            ->method('editPropertyValues')
            ->with($subclassOfProperty, $toClass)
            ->willReturn(true);

        $classMovedEvent = $this->createMock(ClassMovedEvent::class);
        $classMovedEvent
            ->method('getName')
            ->willReturn(ClassMovedEvent::class);
        $classMovedEvent
            ->method('getClass')
            ->willReturn($fromClass);

        $this->eventManager
            ->expects($this->once())
            ->method('trigger')
            ->with(new ClassMovedEvent($fromClass));

        if ($issetPermissionCopier) {
            $this->sut->withPermissionCopier($this->permissionCopier);
        }

        $resourceTransferCommand
            ->expects($issetPermissionCopier ? $this->once() : $this->never())
            ->method('useDestinationAcl')
            ->willReturn($useDestinationAcl);

        $changePermissions = $issetPermissionCopier && $useDestinationAcl;

        $fromSubClass = $this->createMock(core_kernel_classes_Class::class);
        $fromSubClass
            ->expects($changePermissions ? $this->once() : $this->never())
            ->method('getInstances')
            ->willReturn([$this->createMock(core_kernel_classes_Resource::class)]);
        $fromSubClass
            ->expects($changePermissions ? $this->once() : $this->never())
            ->method('getSubClasses')
            ->willReturn([]);

        $fromClass
            ->expects($changePermissions ? $this->once() : $this->never())
            ->method('getInstances')
            ->willReturn([$this->createMock(core_kernel_classes_Resource::class)]);
        $fromClass
            ->expects($changePermissions ? $this->once() : $this->never())
            ->method('getSubClasses')
            ->willReturn([$fromSubClass]);

        $this->permissionCopier
            ->expects($changePermissions ? $this->exactly(4) : $this->never())
            ->method('copy');

        $fromClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fromClassUri');

        $this->assertEquals(
            new ResourceTransferResult('fromClassUri'),
            $this->sut->transfer($resourceTransferCommand)
        );
    }

    public function testTransferAssertIsNotRootClass(): void
    {
        $resourceTransferCommand = $this->createMock(ResourceTransferCommand::class);
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getFrom')
            ->willReturn('fromClassUri');
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getTo')
            ->willReturn('toClassUri');

        $fromClass = $this->createMock(core_kernel_classes_Class::class);
        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getClass')
            ->willReturnCallback(
                fn (string $uri): core_kernel_classes_Class => $uri === 'fromClassUri' ? $fromClass : $toClass
            );

        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->with($fromClass)
            ->willReturn(true);

        $fromClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fromClassUri');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root class "fromClassUri" cannot be moved');

        $this->sut->transfer($resourceTransferCommand);
    }

    public function testTransferAssertIsInSameRootClass(): void
    {
        $resourceTransferCommand = $this->createMock(ResourceTransferCommand::class);
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getFrom')
            ->willReturn('fromClassUri');
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getTo')
            ->willReturn('toClassUri');

        $fromClass = $this->createMock(core_kernel_classes_Class::class);
        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getClass')
            ->willReturnCallback(
                fn (string $uri): core_kernel_classes_Class => $uri === 'fromClassUri' ? $fromClass : $toClass
            );

        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->with($fromClass)
            ->willReturn(false);

        $rootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$rootClass]);

        $fromClass
            ->expects($this->once())
            ->method('equals')
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

        $fromClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fromClassUri');

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
            'Selected class (fromClassUri) and destination class (toClassUri) must be in the same root class '
            . '(rootClassUri).'
        );

        $this->sut->transfer($resourceTransferCommand);
    }

    public function testTransferAssertIsNotSameClass(): void
    {
        $resourceTransferCommand = $this->createMock(ResourceTransferCommand::class);
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getFrom')
            ->willReturn('fromClassUri');
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getTo')
            ->willReturn('toClassUri');

        $fromClass = $this->createMock(core_kernel_classes_Class::class);
        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getClass')
            ->willReturnCallback(
                fn (string $uri): core_kernel_classes_Class => $uri === 'fromClassUri' ? $fromClass : $toClass
            );

        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->with($fromClass)
            ->willReturn(false);

        $rootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$rootClass]);

        $fromClass
            ->expects($this->exactly(2))
            ->method('equals')
            ->willReturnCallback(fn (core_kernel_classes_Class $class) => $class === $toClass);

        $fromClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fromClassUri');

        $toClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('toClassUri');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Selected class (fromClassUri) and destination class (toClassUri) cannot be the same class.'
        );

        $this->sut->transfer($resourceTransferCommand);
    }

    public function testTransferAssertIsNotSubclass(): void
    {
        $resourceTransferCommand = $this->createMock(ResourceTransferCommand::class);
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getFrom')
            ->willReturn('fromClassUri');
        $resourceTransferCommand
            ->expects($this->once())
            ->method('getTo')
            ->willReturn('toClassUri');

        $fromClass = $this->createMock(core_kernel_classes_Class::class);
        $toClass = $this->createMock(core_kernel_classes_Class::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getClass')
            ->willReturnCallback(
                fn (string $uri): core_kernel_classes_Class => $uri === 'fromClassUri' ? $fromClass : $toClass
            );

        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->with($fromClass)
            ->willReturn(false);

        $rootClass = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassesListService
            ->expects($this->once())
            ->method('list')
            ->willReturn([$rootClass]);

        $fromClass
            ->expects($this->exactly(2))
            ->method('equals')
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
            ->expects($this->exactly(2))
            ->method('isSubClassOf')
            ->willReturn(true);

        $fromClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('fromClassUri');

        $toClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('toClassUri');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The destination class (toClassUri) cannot be a subclass of the selected class (fromClassUri).'
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
