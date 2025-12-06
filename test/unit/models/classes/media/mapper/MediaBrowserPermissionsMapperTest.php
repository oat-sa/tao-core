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

namespace oat\tao\test\unit\models\classes\media\mapper;

use oat\generis\test\ServiceManagerMockTrait;
use oat\tao\model\media\mapper\MediaBrowserPermissionsMapper;
use PHPUnit\Framework\TestCase;
use oat\tao\model\accessControl\PermissionChecker;
use oat\tao\model\accessControl\PermissionCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class MediaBrowserPermissionsMapperTest extends TestCase
{
    use ServiceManagerMockTrait;

    private MediaBrowserPermissionsMapper $subject;
    private PermissionCheckerInterface|MockObject $permissionChecker;

    protected function setUp(): void
    {
        $this->permissionChecker = $this->createMock(PermissionCheckerInterface::class);
        $this->subject = new MediaBrowserPermissionsMapper();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    PermissionChecker::class => $this->permissionChecker,
                ]
            )
        );
    }

    public function testMapWithoutAccessControlEnabledGrantReadAndWritePermissions(): void
    {
        $this->assertEquals(
            [
                'permissions' => [
                    PermissionCheckerInterface::PERMISSION_READ,
                    PermissionCheckerInterface::PERMISSION_WRITE,
                ]
            ],
            $this->subject->map([], 'uri')
        );
    }

    public function testMapWithAccessControlEnabledGrantReadAndWritePermissions(): void
    {
        $resourceUri = 'uri';

        $this->subject->enableAccessControl();

        $this->permissionChecker
            ->expects($this->exactly(1))
            ->method('hasReadAccess')
            ->with($resourceUri)
            ->willReturn(false);

        $this->permissionChecker
            ->expects($this->exactly(1))
            ->method('hasWriteAccess')
            ->with($resourceUri)
            ->willReturn(false);

        $this->assertEquals(
            [
                'permissions' => []
            ],
            $this->subject->map([], $resourceUri)
        );
    }
}
