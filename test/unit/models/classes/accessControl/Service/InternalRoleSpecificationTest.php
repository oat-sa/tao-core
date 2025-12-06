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

use core_kernel_classes_Resource;
use PHPUnit\Framework\TestCase;
use oat\tao\model\accessControl\Service\InternalRoleSpecification;
use oat\tao\model\user\TaoRoles;

class InternalRoleSpecificationTest extends TestCase
{
    /** @var InternalRoleSpecification */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new InternalRoleSpecification();
    }

    public function testIsSatisfiedByWithManagementRole(): void
    {
        $this->assertTrue($this->subject->isSatisfiedBy($this->createRole(TaoRoles::BACK_OFFICE)));
    }

    public function testIsSatisfiedByWithoutManagementRole(): void
    {
        $this->assertFalse($this->subject->isSatisfiedBy($this->createRole('custom')));
    }

    private function createRole(string $uri): core_kernel_classes_Resource
    {
        $role = $this->createMock(core_kernel_classes_Resource::class);

        $role->method('getUri')
            ->willReturn($uri);

        return $role;
    }
}
