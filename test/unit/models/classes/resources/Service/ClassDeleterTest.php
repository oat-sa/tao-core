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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use ArrayIterator;
use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\resources\relation\ResourceRelation;
use oat\tao\model\resources\relation\ResourceRelationCollection;
use oat\tao\model\resources\relation\service\ResourceRelationServiceProxy;
use oat\tao\model\TaoOntology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\ClassDeleter;
use oat\tao\model\accessControl\PermissionCheckerInterface;
use oat\tao\model\Specification\ClassSpecificationInterface;
use oat\tao\model\resources\Exception\ClassDeletionException;
use oat\tao\model\resources\Exception\PartialClassDeletionException;
use oat\generis\model\resource\Contract\ResourceRepositoryInterface;

/**
 * @TODO Refactor tests - code duplicates
 */
class ClassDeleterTest extends TestCase
{
    private ClassDeleter $sut;
    private ClassSpecificationInterface|MockObject $rootClassSpecification;
    private PermissionCheckerInterface|MockObject $permissionChecker;
    private Ontology|MockObject $ontology;
    private ResourceRepositoryInterface|MockObject $resourceRepository;
    private ResourceRepositoryInterface|MockObject $classRepository;
    private ResourceRelationServiceProxy|MockObject $resourceRelationServiceProxyMock;

    protected function setUp(): void
    {
        $this->rootClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->permissionChecker = $this->createMock(PermissionCheckerInterface::class);

        $this->ontology = $this->createMock(Ontology::class);
        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $this->resourceRepository = $this->createMock(ResourceRepositoryInterface::class);
        $this->classRepository = $this->createMock(ResourceRepositoryInterface::class);
        $this->resourceRelationServiceProxyMock = $this->createMock(
            ResourceRelationServiceProxy::class
        );

        $this->sut = new ClassDeleter(
            $this->rootClassSpecification,
            $this->permissionChecker,
            $this->ontology,
            $this->resourceRepository,
            $this->classRepository,
            $this->resourceRelationServiceProxyMock
        );
    }

    public function testDeleteFullAccess(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturn(true);
        $this->permissionChecker
            ->expects($this->exactly(4))
            ->method('hasWriteAccess')
            ->willReturn(true);

        $this->resourceRepository
            ->expects($this->exactly(4))
            ->method('delete');
        $this->classRepository
            ->expects($this->exactly(2))
            ->method('delete');

        $subClassInstance = $this->createMock(core_kernel_classes_Resource::class);
        $subClassInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassInstanceUri');
        $subClassInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClassProperty = $this->createMock(core_kernel_classes_Property::class);
        $subClassProperty
            ->expects($this->once())
            ->method('getPropertyValues')
            ->willReturn(['subClassPropertyIndexUri']);
        $subClassProperty
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $subClass = $this->createMock(core_kernel_classes_Class::class);
        $subClass
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$subClassInstance]);
        $subClass
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn([$subClassProperty]);

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $classProperty = $this->createMock(core_kernel_classes_Property::class);
        $classProperty
            ->expects($this->once())
            ->method('getPropertyValues')
            ->willReturn(['classPropertyIndexUri']);
        $classProperty
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn([$classProperty]);

        $classPropertyIndexResource = $this->createMock(core_kernel_classes_Resource::class);//
        $subClassPropertyIndexResource = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getResource')
            ->willReturnCallback(
                function (string $uri) use (
                    $classPropertyIndexResource,
                    $subClassPropertyIndexResource
                ): core_kernel_classes_Resource {
                    if ($uri === 'classPropertyIndexUri') {
                        return $classPropertyIndexResource;
                    }

                    if ($uri === 'subClassPropertyIndexUri') {
                        return $subClassPropertyIndexResource;
                    }

                    return $this->createMock(core_kernel_classes_Resource::class);
                }
            );

        $this->sut->delete($class);
    }

    public function testDeleteRootClass(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->permissionChecker
            ->expects($this->never())
            ->method('hasReadAccess');

        $this->resourceRepository
            ->expects($this->never())
            ->method('delete');
        $this->classRepository
            ->expects($this->never())
            ->method('delete');

        $this->expectException(ClassDeletionException::class);

        $this->sut->delete($this->createMock(core_kernel_classes_Class::class));
    }

    public function testDeleteNoAccessToClass(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->once())
            ->method('hasReadAccess')
            ->willReturn(false);

        $this->resourceRepository
            ->expects($this->never())
            ->method('delete');
        $this->classRepository
            ->expects($this->never())
            ->method('delete');

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->never())
            ->method('getSubClasses');
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->expectException(PartialClassDeletionException::class);

        $this->sut->delete($class);
    }

    public function testDeleteNoAccessToSubClass(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturnCallback(
                static function (string $uri): bool {
                    return $uri === 'classUri';
                }
            );
        $this->permissionChecker
            ->expects($this->once())
            ->method('hasWriteAccess')
            ->with('classInstanceUri')
            ->willReturn(true);

        $this->resourceRepository
            ->expects($this->once())
            ->method('delete');
        $this->classRepository
            ->expects($this->never())
            ->method('delete');

        $subClass = $this->createMock(core_kernel_classes_Class::class);
        $subClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->never())
            ->method('getSubClasses');

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->never())
            ->method('getProperties');
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->expectException(PartialClassDeletionException::class);

        $this->sut->delete($class);
    }

    public function testDeleteReadAccessToClasses(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturn(true);
        $this->permissionChecker
            ->expects($this->exactly(3))
            ->method('hasWriteAccess')
            ->willReturnCallback(
                static function (string $uri): bool {
                    return $uri !== 'subClassUri';
                }
            );

        $this->resourceRepository
            ->expects($this->exactly(2))
            ->method('delete');
        $this->classRepository
            ->expects($this->never())
            ->method('delete');

        $subClassInstance = $this->createMock(core_kernel_classes_Resource::class);
        $subClassInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassInstanceUri');
        $subClassInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClass = $this->createMock(core_kernel_classes_Class::class);
        $subClass
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$subClassInstance]);
        $subClass
            ->expects($this->never())
            ->method('getProperties');

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->never())
            ->method('getProperties');
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->expectException(PartialClassDeletionException::class);

        $this->sut->delete($class);
    }

    public function testDeleteWithoutAccessToClassInstances(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturn(true);
        $this->permissionChecker
            ->expects($this->exactly(3))
            ->method('hasWriteAccess')
            ->willReturnCallback(
                static function (string $uri): bool {
                    return $uri !== 'classInstanceUri';
                }
            );

        $this->resourceRepository
            ->expects($this->exactly(2))
            ->method('delete');
        $this->classRepository
            ->expects($this->once())
            ->method('delete');

        $subClassInstance = $this->createMock(core_kernel_classes_Resource::class);
        $subClassInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassInstanceUri');
        $subClassInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClassProperty = $this->createMock(core_kernel_classes_Property::class);
        $subClassProperty
            ->expects($this->once())
            ->method('getPropertyValues')
            ->willReturn(['subClassPropertyIndexUri']);
        $subClassProperty
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $subClass = $this->createMock(core_kernel_classes_Class::class);
        $subClass
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$subClassInstance]);
        $subClass
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn([$subClassProperty]);

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->never())
            ->method('getProperties');
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClassPropertyIndexResource = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('subClassPropertyIndexUri')
            ->willReturn($subClassPropertyIndexResource);

        $this->expectException(PartialClassDeletionException::class);

        $this->sut->delete($class);
    }

    public function testDeleteWithoutAccessToSubClassInstances(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturn(true);
        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasWriteAccess')
            ->willReturnCallback(
                static function (string $uri): bool {
                    return $uri === 'classInstanceUri';
                }
            );

        $this->resourceRepository
            ->expects($this->once())
            ->method('delete');
        $this->classRepository
            ->expects($this->never())
            ->method('delete');

        $subClassInstance = $this->createMock(core_kernel_classes_Resource::class);
        $subClassInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassInstanceUri');
        $subClassInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClass = $this->createMock(core_kernel_classes_Class::class);
        $subClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$subClassInstance]);
        $subClass
            ->expects($this->never())
            ->method('getProperties');

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->never())
            ->method('getProperties');
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->ontology
            ->expects($this->never())
            ->method('getResource');

        $this->expectException(PartialClassDeletionException::class);

        $this->sut->delete($class);
    }

    public function testDeleteWithoutWriteAccessToClass(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturn(true);
        $this->permissionChecker
            ->expects($this->exactly(4))
            ->method('hasWriteAccess')
            ->willReturnCallback(
                static function (string $uri): bool {
                    return $uri !== 'classUri';
                }
            );

        $this->resourceRepository
            ->expects($this->exactly(3))
            ->method('delete');
        $this->classRepository
            ->expects($this->once())
            ->method('delete');

        $subClassInstance = $this->createMock(core_kernel_classes_Resource::class);
        $subClassInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassInstanceUri');
        $subClassInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClassProperty = $this->createMock(core_kernel_classes_Property::class);
        $subClassProperty
            ->expects($this->once())
            ->method('getPropertyValues')
            ->willReturn(['subClassPropertyIndexUri']);
        $subClassProperty
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $subClass = $this->createMock(core_kernel_classes_Class::class);
        $subClass
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$subClassInstance]);
        $subClass
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn([$subClassProperty]);

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->never())
            ->method('getProperties');
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClassPropertyIndexResource = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('subClassPropertyIndexUri')
            ->willReturn($subClassPropertyIndexResource);

        $this->expectException(PartialClassDeletionException::class);

        $this->sut->delete($class);
    }

    public function testDeleteWithoutWriteAccessToSubClass(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturn(true);
        $this->permissionChecker
            ->expects($this->exactly(3))
            ->method('hasWriteAccess')
            ->willReturnCallback(
                static function (string $uri): bool {
                    return $uri !== 'subClassUri';
                }
            );

        $this->resourceRepository
            ->expects($this->exactly(2))
            ->method('delete');
        $this->classRepository
            ->expects($this->never())
            ->method('delete');

        $subClassInstance = $this->createMock(core_kernel_classes_Resource::class);
        $subClassInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassInstanceUri');
        $subClassInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $subClass = $this->createMock(core_kernel_classes_Class::class);
        $subClass
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$subClassInstance]);
        $subClass
            ->expects($this->never())
            ->method('getProperties');

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->never())
            ->method('getProperties');
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->ontology
            ->expects($this->never())
            ->method('getResource');

        $this->expectException(PartialClassDeletionException::class);

        $this->sut->delete($class);
    }

    public function testDeleteClassWithResourceWithRelations(): void
    {
        $class = $this->createMock(core_kernel_classes_Class::class);

        $this->rootClassSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->permissionChecker
            ->method('hasReadAccess')
            ->willReturn(true);

        $class->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);

        $classResource = $this->createMock(core_kernel_classes_Resource::class);
        $class->expects($this->once())
            ->method('getInstances')
            ->willReturn([
                'resourceRelationId' => $classResource,
                'not-used-id' => $classResource
            ]);

        $class->method('getRootId')
            ->willReturn(TaoOntology::CLASS_URI_ITEM);

        $resourceRelationCollectionMock = $this->createMock(ResourceRelationCollection::class);

        $resourceRelationCollectionMock->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn([
                $resourceRelationCollectionMock
            ]);

        $this->resourceRelationServiceProxyMock->expects($this->once())
            ->method('findRelations')
            ->willReturn($resourceRelationCollectionMock);

        $resourceRelationMock = $this->createMock(ResourceRelation::class);

        $resourceRelationCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([
                $resourceRelationMock,
                $resourceRelationMock
            ]));

        $resourceRelationMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturnOnConsecutiveCalls(
                'resourceRelationId',
                'some-mistake'
            );

        $classResource
            ->method('exists')
            ->willReturn(true);

        $this->permissionChecker->expects($this->once())
            ->method('hasWriteAccess')
            ->willReturn(true);

        $this->resourceRepository
            ->expects($this->once())
            ->method('delete');

        $this->classRepository->expects($this->never())
            ->method('delete');

        $class->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->expectException(PartialClassDeletionException::class);
        $this->sut->delete($class);
    }
}
