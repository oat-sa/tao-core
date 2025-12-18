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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\accessControl\Service;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use PHPUnit\Framework\TestCase;
use oat\tao\model\accessControl\Service\DeleteRoleService;
use oat\tao\model\accessControl\Service\InternalRoleSpecification;
use oat\tao\model\exceptions\UserErrorException;
use PHPUnit\Framework\MockObject\MockObject;
use tao_models_classes_RoleService;

class DeleteRoleServiceTest extends TestCase
{
    /** @var DeleteRoleService */
    private $subject;

    /** @var tao_models_classes_RoleService|MockObject */
    private $roleService;

    /** @var InternalRoleSpecification|MockObject */
    private $internalRoleSpecification;

    protected function setUp(): void
    {
        $this->internalRoleSpecification = $this->createMock(InternalRoleSpecification::class);
        $this->roleService = $this->createMock(tao_models_classes_RoleService::class);
        $this->subject = new DeleteRoleService(
            $this->internalRoleSpecification,
            $this->roleService
        );
    }

    public function testDeleteWillThrowExceptionForNotWritableRoleWithoutDuplication(): void
    {
        $this->expectException(UserErrorException::class);

        $role = $this->createRole(false, 'does not matter');
        $role->method('removePropertyValues')
            ->willReturn(false);

        $this->subject->delete($role);
    }

    public function testDeleteWillThrowExceptionForWritableAndInternalRole(): void
    {
        $this->expectException(UserErrorException::class);

        $role = $this->createRole(true, 'does not matter');

        $this->internalRoleSpecification
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $this->subject->delete($role);
    }

    public function testDeleteWillThrowExceptionWritableRoleAssociatedToUsers(): void
    {
        $this->expectException(UserErrorException::class);

        $role = $this->createRole(true, 'does not matter', ['user1']);

        $this->internalRoleSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->subject->delete($role);
    }

    public function testDeleteWillThrowExceptionWritableRoleWhenCannotRemoveRole(): void
    {
        $this->expectException(UserErrorException::class);

        $role = $this->createRole(true, 'does not matter');

        $this->internalRoleSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->roleService
            ->expects($this->once())
            ->method('removeRole')
            ->willReturn(false);

        $this->subject->delete($role);
    }

    public function testDeleteSuccessfullyWritableRole(): void
    {
        $role = $this->createRole(true, 'does not matter');

        $this->internalRoleSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $this->roleService
            ->expects($this->once())
            ->method('removeRole')
            ->willReturn(true);

        $this->subject->delete($role);
    }

    public function testDeleteSuccessfullyNotWritableAndDuplicatedRole(): void
    {
        $role = $this->createRole(false, 'does not matter');

        $this->internalRoleSpecification
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $role->expects($this->once())
            ->method('removePropertyValues')
            ->willReturn(true);

        $this->subject->delete($role);
    }

    /**
     * @return core_kernel_classes_Resource|MockObject
     */
    private function createRole(bool $isWritable, string $uri, array $users = []): core_kernel_classes_Resource
    {
        $class = $this->createMock(core_kernel_classes_Class::class);

        $class->method('searchInstances')
            ->willReturn($users);

        $role = $this->createMock(core_kernel_classes_Resource::class);

        $role->method('getUri')
            ->willReturn($uri);

        $role->method('isWritable')
            ->willReturn($isWritable);

        $role->method('getProperty')
            ->with(OntologyRdfs::RDFS_LABEL)
            ->willReturn($this->createMock(core_kernel_classes_Property::class));

        $role->method('getClass')
            ->willReturn($class);

        return $role;
    }
}
