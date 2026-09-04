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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\user;

use common_exception_Unauthorized;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\accessControl\PermissionCheckerInterface;
use oat\tao\model\user\CommentMentionUserSearchService;
use oat\tao\model\user\MentionEligibleUsersProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use tao_models_classes_UserService;

class CommentMentionUserSearchServiceTest extends TestCase
{
    private Ontology|MockObject $ontology;
    private PermissionCheckerInterface|MockObject $permissionChecker;
    private tao_models_classes_UserService|MockObject $userService;
    private MentionEligibleUsersProviderInterface|MockObject $eligibleUsersProvider;
    private CommentMentionUserSearchService $sut;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->permissionChecker = $this->createMock(PermissionCheckerInterface::class);
        $this->userService = $this->createMock(tao_models_classes_UserService::class);
        $this->eligibleUsersProvider = $this->createMock(MentionEligibleUsersProviderInterface::class);
        $this->sut = new CommentMentionUserSearchService(
            $this->ontology,
            $this->permissionChecker,
            $this->userService,
            $this->eligibleUsersProvider
        );
    }

    public function testSearchThrowsWhenCurrentUserLacksReadAccess(): void
    {
        $this->permissionChecker
            ->expects($this->once())
            ->method('hasReadAccess')
            ->with('http://example.test/item#1')
            ->willReturn(false);

        $this->eligibleUsersProvider->expects($this->never())->method('getEligibleUserUris');

        $this->expectException(common_exception_Unauthorized::class);

        $this->sut->search('http://example.test/item#1', 'item', 'ali');
    }

    public function testSearchThrowsOnInvalidResourceType(): void
    {
        $this->permissionChecker
            ->method('hasReadAccess')
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);

        $this->sut->search('http://example.test/item#1', 'delivery', 'ali');
    }

    public function testSearchReturnsEmptyWhenProviderReturnsEmptyList(): void
    {
        $this->permissionChecker
            ->expects($this->once())
            ->method('hasReadAccess')
            ->with('http://example.test/item#1')
            ->willReturn(true);

        $this->eligibleUsersProvider
            ->method('getEligibleUserUris')
            ->with('http://example.test/item#1')
            ->willReturn([]);

        $this->userService->expects($this->never())->method('getAllUsers');

        $result = $this->sut->search('http://example.test/item#1', 'item', 'ali', 10, 5);

        $this->assertSame([], $result['users']);
        $this->assertSame(0, $result['total']);
        $this->assertSame(10, $result['limit']);
        $this->assertSame(5, $result['offset']);
    }

    public function testOpenModeMatchesLoginViaGetAllUsers(): void
    {
        $this->permissionChecker
            ->expects($this->once())
            ->method('hasReadAccess')
            ->with('http://example.test/item#1')
            ->willReturn(true);

        $this->eligibleUsersProvider
            ->method('getEligibleUserUris')
            ->willReturn(null);

        $user = $this->createUserResourceMock(
            'http://example.test/user#alice',
            'alice',
            'Alice',
            'Smith'
        );

        $this->userService
            ->expects($this->once())
            ->method('getAllUsers')
            ->willReturn([$user]);

        $result = $this->sut->search('http://example.test/item#1', 'item', 'ali');

        $this->assertSame(1, $result['total']);
        $this->assertSame('http://example.test/user#alice', $result['users'][0]['id']);
        $this->assertSame('alice', $result['users'][0]['login']);
        $this->assertSame('Alice Smith', $result['users'][0]['displayName']);
    }

    public function testRestrictedModeMatchesDisplayName(): void
    {
        $this->permissionChecker
            ->method('hasReadAccess')
            ->willReturn(true);

        $this->eligibleUsersProvider
            ->method('getEligibleUserUris')
            ->willReturn(['http://example.test/user#bob']);

        $user = $this->createUserResourceMock(
            'http://example.test/user#bob',
            'bob',
            'Robert',
            'Jones'
        );

        $this->ontology
            ->method('getResource')
            ->with('http://example.test/user#bob')
            ->willReturn($user);

        $this->userService->expects($this->never())->method('getAllUsers');

        $result = $this->sut->search('http://example.test/item#1', 'item', 'robert');

        $this->assertSame(1, $result['total']);
        $this->assertSame('bob', $result['users'][0]['login']);
        $this->assertSame('Robert Jones', $result['users'][0]['displayName']);
    }

    public function testRestrictedModeExcludesNonMatchingQuery(): void
    {
        $this->permissionChecker
            ->method('hasReadAccess')
            ->willReturn(true);

        $this->eligibleUsersProvider
            ->method('getEligibleUserUris')
            ->willReturn(['http://example.test/user#bob']);

        $user = $this->createUserResourceMock(
            'http://example.test/user#bob',
            'bob',
            'Robert',
            'Jones',
            'bob@example.test'
        );

        $this->ontology
            ->method('getResource')
            ->willReturn($user);

        $result = $this->sut->search('http://example.test/item#1', 'item', 'zzz');

        $this->assertSame([], $result['users']);
        $this->assertSame(0, $result['total']);
    }

    public function testExcludesUsersWithoutValidEmail(): void
    {
        $this->permissionChecker
            ->method('hasReadAccess')
            ->willReturn(true);

        $this->eligibleUsersProvider
            ->method('getEligibleUserUris')
            ->willReturn(['http://example.test/user#no-mail']);

        $user = $this->createUserResourceMock(
            'http://example.test/user#no-mail',
            'nomail',
            'No',
            'Mail',
            ''
        );

        $this->ontology
            ->method('getResource')
            ->willReturn($user);

        $result = $this->sut->search('http://example.test/item#1', 'item', 'nomail');

        $this->assertSame([], $result['users']);
        $this->assertSame(0, $result['total']);
    }

    /**
     * @return core_kernel_classes_Resource&MockObject
     */
    private function createUserResourceMock(
        string $uri,
        string $login,
        string $firstName,
        string $lastName,
        string $email = 'user@example.test'
    ): core_kernel_classes_Resource {
        $loginProperty = $this->createMock(core_kernel_classes_Property::class);
        $firstNameProperty = $this->createMock(core_kernel_classes_Property::class);
        $lastNameProperty = $this->createMock(core_kernel_classes_Property::class);
        $labelProperty = $this->createMock(core_kernel_classes_Property::class);
        $mailProperty = $this->createMock(core_kernel_classes_Property::class);

        $this->ontology
            ->method('getProperty')
            ->willReturnMap([
                [GenerisRdf::PROPERTY_USER_LOGIN, $loginProperty],
                [GenerisRdf::PROPERTY_USER_FIRSTNAME, $firstNameProperty],
                [GenerisRdf::PROPERTY_USER_LASTNAME, $lastNameProperty],
                [OntologyRdfs::RDFS_LABEL, $labelProperty],
                [GenerisRdf::PROPERTY_USER_MAIL, $mailProperty],
            ]);

        $user = $this->createMock(core_kernel_classes_Resource::class);
        $user->method('getUri')->willReturn($uri);
        $user->method('exists')->willReturn(true);
        $user->method('getOnePropertyValue')->willReturnCallback(
            static function ($property) use (
                $loginProperty,
                $firstNameProperty,
                $lastNameProperty,
                $labelProperty,
                $mailProperty,
                $login,
                $firstName,
                $lastName,
                $email
            ) {
                if ($property === $loginProperty) {
                    return $login;
                }
                if ($property === $firstNameProperty) {
                    return $firstName;
                }
                if ($property === $lastNameProperty) {
                    return $lastName;
                }
                if ($property === $labelProperty) {
                    return '';
                }
                if ($property === $mailProperty) {
                    return $email;
                }

                return '';
            }
        );

        return $user;
    }
}
