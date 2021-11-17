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

use core_kernel_classes_Class;
use oat\generis\test\TestCase;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\ClassDeleter;
use oat\tao\model\accessControl\PermissionCheckerInterface;
use oat\tao\model\Specification\ClassSpecificationInterface;

/**
 * @TODO Improve tests
 */
class ClassDeleterTest extends TestCase
{
    /** @var ClassDeleter */
    private $sut;

    /** @var ClassSpecificationInterface|MockObject */
    private $rootClassSpecification;

    /** @var PermissionCheckerInterface|MockObject */
    private $permissionChecker;

    /** @var Ontology|MockObject */
    private $ontology;

    protected function setUp(): void
    {
        $this->rootClassSpecification = $this->createMock(ClassSpecificationInterface::class);
        $this->permissionChecker = $this->createMock(PermissionCheckerInterface::class);
        $this->ontology = $this->createMock(Ontology::class);
        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $this->sut = new ClassDeleter($this->rootClassSpecification, $this->permissionChecker, $this->ontology);
    }

    public function testDeleteFullAccess(): void
    {
        $this->rootClassSpecification
            ->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $subClassInstance = $this->createMock(core_kernel_classes_Resource::class);
        $subClassInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassInstanceUri');
        $subClassInstance
            ->expects($this->once())
            ->method('delete')
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
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([]);
        $subClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('subClassUri');
        $subClass
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$subClassInstance]);
        $subClass
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn([$subClassProperty]);
        $subClass
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $classInstance = $this->createMock(core_kernel_classes_Resource::class);
        $classInstance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classInstanceUri');
        $classInstance
            ->expects($this->once())
            ->method('delete')
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
            ->expects($this->once())
            ->method('getSubClasses')
            ->willReturn([$subClass]);
        $class
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('classUri');
        $class
            ->expects($this->once())
            ->method('getInstances')
            ->willReturn([$classInstance]);
        $class
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn([$classProperty]);
        $class
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);
        $class
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(2))
            ->method('hasReadAccess')
            ->willReturn(true);
        $this->permissionChecker
            ->expects($this->exactly(4))
            ->method('hasWriteAccess')
            ->willReturn(true);

        $classPropertyIndexResource = $this->createMock(core_kernel_classes_Resource::class);
        $classPropertyIndexResource
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $subClassPropertyIndexResource = $this->createMock(core_kernel_classes_Resource::class);
        $subClassPropertyIndexResource
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $this->ontology
            ->expects($this->exactly(2))
            ->method('getResource')
            ->willReturnCallback(
                static function (string $uri) use (
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
        $this->assertTrue($this->sut->isDeleted($class));
    }
}
