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

namespace oat\tao\test\unit\accessControl;

use common_test_TestUser as TestUser;
use oat\generis\test\ServiceManagerMockTrait;
use oat\tao\model\accessControl\Context;
use oat\oatbox\log\logger\AdvancedLogger;
use oat\tao\model\Context\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\accessControl\ActionAccessControl;
use PHPUnit\Framework\TestCase;

class ActionAccessControlTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const TEST_CONTROLLER = 'testController';
    private const TEST_ACTION = 'testAction';

    private ActionAccessControl $sut;
    private TestUser $user;
    private ContextInterface|MockObject $context;

    protected function setUp(): void
    {
        $this->sut = new ActionAccessControl();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    AdvancedLogger::ACL_SERVICE_ID => $this->createMock(AdvancedLogger::class),
                ]
            )
        );

        $this->user = $this->createUser();
        $this->user->setRoles(['role1']);

        $this->context = $this->createMock(ContextInterface::class);
        $this->context
            ->method('getParameter')
            ->willReturnCallback(
                function (string $parameter) {
                    if ($parameter === Context::PARAM_CONTROLLER) {
                        return self::TEST_CONTROLLER;
                    }
                    if ($parameter === Context::PARAM_ACTION) {
                        return self::TEST_ACTION;
                    }
                    if ($parameter === Context::PARAM_USER) {
                        return $this->user;
                    }

                    return null;
                }
            );
    }

    public function testAddPermissions(): void
    {
        $this->assertEmpty($this->getActionAccessControlPermissions());

        $this->sut->addPermissions([
            self::TEST_CONTROLLER => [self::TEST_ACTION => ['role1' => 'READ']],
        ]);
        $this->assertEquals(
            [self::TEST_CONTROLLER => [self::TEST_ACTION => ['role1' => 'READ']]],
            $this->getActionAccessControlPermissions()
        );

        $this->sut->addPermissions([
            self::TEST_CONTROLLER => [self::TEST_ACTION => ['role2' => 'WRITE']],
        ]);
        $this->assertEquals(
            [self::TEST_CONTROLLER => [self::TEST_ACTION => ['role1' => 'READ', 'role2' => 'WRITE']]],
            $this->getActionAccessControlPermissions()
        );

        $this->sut->addPermissions([
            self::TEST_CONTROLLER => [self::TEST_ACTION => ['role1' => 'GRANT']],
        ]);
        $this->assertEquals(
            [self::TEST_CONTROLLER => [self::TEST_ACTION => ['role1' => 'GRANT', 'role2' => 'WRITE']]],
            $this->getActionAccessControlPermissions()
        );
    }

    public function testRemovePermissions(): void
    {
        $this->assertEmpty($this->getActionAccessControlPermissions());

        $this->configureActionAccessControl(['role1' => 'READ', 'role2' => 'WRITE', 'role3' => 'GRANT']);
        $this->assertEquals(
            [
                self::TEST_CONTROLLER => [
                    self::TEST_ACTION => [
                        'role1' => 'READ',
                        'role2' => 'WRITE',
                        'role3' => 'GRANT'
                    ]
                ]
            ],
            $this->getActionAccessControlPermissions()
        );

        $this->sut->removePermissions([
            self::TEST_CONTROLLER => [self::TEST_ACTION => ['role1']],
        ]);
        $this->assertEquals(
            [self::TEST_CONTROLLER => [self::TEST_ACTION => ['role2' => 'WRITE', 'role3' => 'GRANT']]],
            $this->getActionAccessControlPermissions()
        );

        $this->sut->removePermissions([
            self::TEST_CONTROLLER => [self::TEST_ACTION => ['role2', 'role3']],
        ]);
        $this->assertEmpty($this->getActionAccessControlPermissions());
    }

    public function testHasReadAccess(): void
    {
        $this->configureActionAccessControl(['role1' => ActionAccessControl::READ]);
        $this->assertTrue($this->hasReadAccess());

        $this->configureActionAccessControl(['role1' => ActionAccessControl::WRITE]);
        $this->assertTrue($this->hasReadAccess());

        $this->configureActionAccessControl(['role1' => ActionAccessControl::GRANT]);
        $this->assertTrue($this->hasReadAccess());

        $this->configureActionAccessControl([]);
        $this->assertTrue($this->hasReadAccess());
    }

    public function testHasWriteAccess(): void
    {
        $this->configureActionAccessControl(['role1' => ActionAccessControl::READ]);
        $this->assertNotTrue($this->hasWriteAccess());

        $this->configureActionAccessControl(['role1' => ActionAccessControl::WRITE]);
        $this->assertTrue($this->hasWriteAccess());

        $this->configureActionAccessControl(['role1' => ActionAccessControl::GRANT]);
        $this->assertTrue($this->hasWriteAccess());

        $this->configureActionAccessControl([]);
        $this->assertTrue($this->hasWriteAccess());
    }

    public function testHasGrantAccess(): void
    {
        $this->configureActionAccessControl(['role1' => ActionAccessControl::READ]);
        $this->assertNotTrue($this->hasGrantAccess());

        $this->configureActionAccessControl(['role1' => ActionAccessControl::WRITE]);
        $this->assertNotTrue($this->hasGrantAccess());

        $this->configureActionAccessControl(['role1' => ActionAccessControl::GRANT]);
        $this->assertTrue($this->hasGrantAccess());

        $this->configureActionAccessControl([]);
        $this->assertTrue($this->hasGrantAccess());
    }

    private function createUser(): TestUser
    {
        return new class () extends TestUser {
            private $roles;

            public function getRoles()
            {
                return $this->roles;
            }

            public function setRoles(array $roles = []): void
            {
                $this->roles = $roles;
            }
        };
    }

    private function getActionAccessControlPermissions(): array
    {
        return $this->sut->getOption(ActionAccessControl::OPTION_PERMISSIONS, []);
    }

    private function configureActionAccessControl(array $permissions): void
    {
        $this->sut->setOption(ActionAccessControl::OPTION_PERMISSIONS, [
            self::TEST_CONTROLLER => [
                self::TEST_ACTION => $permissions,
            ],
        ]);
    }

    private function hasReadAccess(): bool
    {
        return $this->sut->contextHasReadAccess($this->context);
    }

    private function hasWriteAccess(): bool
    {
        return $this->sut->contextHasWriteAccess($this->context);
    }

    private function hasGrantAccess(): bool
    {
        return $this->sut->contextHasGrantAccess($this->context);
    }
}
