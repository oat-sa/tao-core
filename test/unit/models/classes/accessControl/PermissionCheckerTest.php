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

namespace oat\tao\test\unit\models\classes\accessControl;

use PHPUnit\Framework\TestCase;
use oat\oatbox\user\User;
use oat\tao\model\accessControl\data\DataAccessControl;
use oat\tao\model\accessControl\PermissionChecker;

class PermissionCheckerTest extends TestCase
{
    /** @var PermissionChecker */
    private $subject;

    /** @var DataAccessControl */
    private $dataAccessControl;

    /** @var User */
    private $user;

    protected function setUp(): void
    {
        $this->dataAccessControl = $this->createMock(DataAccessControl::class);
        $this->user = $this->createMock(User::class);
        $this->subject = new PermissionChecker();
        $this->subject->withAccessControl($this->dataAccessControl);
    }

    public function testHasWriteAccess(): void
    {
        $this->mockHasPrivileges(PermissionChecker::PERMISSION_WRITE);

        $this->assertTrue($this->subject->hasWriteAccess('uri', $this->user));
    }

    public function testHasReadAccess(): void
    {
        $this->mockHasPrivileges(PermissionChecker::PERMISSION_READ);

        $this->assertTrue($this->subject->hasReadAccess('uri', $this->user));
    }

    public function testHasGrantAccess(): void
    {
        $this->mockHasPrivileges(PermissionChecker::PERMISSION_GRANT);

        $this->assertTrue($this->subject->hasGrantAccess('uri', $this->user));
    }

    private function mockHasPrivileges(string $permission): void
    {
        $this->dataAccessControl
            ->method('hasPrivileges')
            ->with(
                $this->user,
                [
                    'uri' => $permission
                ]
            )
            ->willReturn(true);
    }
}
