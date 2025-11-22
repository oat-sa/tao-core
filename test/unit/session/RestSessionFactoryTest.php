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
 * Copyright (c) 2018-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\session;

use common_Exception;
use common_http_Request;
use LogicException;
use oat\tao\model\routing\Resolver;
use oat\tao\model\session\restSessionFactory\RestSessionFactory;
use oat\tao\model\session\restSessionFactory\SessionBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;
use tao_actions_RestController;

class RestSessionFactoryTest extends TestCase
{
    public function testCreateSessionFromRequest(): void
    {
        $class = TestRest::class;

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('getControllerClass')
            ->willReturn($class);

        $request = new common_http_Request('http:://fixture.test');

        $sessionBuilder1 = $this->createMock(SessionBuilder::class);
        $sessionBuilder1
            ->method('isApplicable')
            ->with($request)
            ->willReturn(false);

        $sessionBuilder2 = $this->createMock(SessionBuilder::class);
        $sessionBuilder2
            ->method('isApplicable')
            ->with($request)
            ->willReturn(true);
        $sessionBuilder2->expects($this->once())
            ->method('getSession')
            ->with($request);

        $service = new RestSessionFactoryTester();
        $service->builders = [
            $sessionBuilder1,
            $sessionBuilder2,
        ];

        $service->createSessionFromRequest($request, $resolver);
        $this->assertEquals(1, $service->isSessionStarted);
    }

    public function testCreateSessionFromRequestWithInvalidRestController(): void
    {
        $class = stdClass::class;

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('getControllerClass')
            ->willReturn($class);

        $request = new common_http_Request('http:://fixture.test');

        $service = new RestSessionFactoryTester();
        $this->assertFalse($service->createSessionFromRequest($request, $resolver));
    }

    public function testCreateSessionFromRequestWithNoBuilders(): void
    {
        $this->expectException(common_Exception::class);

        $class = TestRest::class;

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('getControllerClass')
            ->willReturn($class);

        $request = new common_http_Request('http:://fixture.test');

        $service = new RestSessionFactoryTester([
            RestSessionFactoryTester::OPTION_BUILDERS => []
        ]);

        $service->createSessionFromRequest($request, $resolver);
    }

    public function testCreateSessionFromRequestWithNoApplicableBuilders(): void
    {
        $this->expectException(common_Exception::class);

        $class = TestRest::class;

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('getControllerClass')
            ->willReturn($class);

        $request = new common_http_Request('http:://fixture.test');

        $builder1 = $this->createMock(SessionBuilder::class);
        $builder1
            ->method('isApplicable')
            ->with($request)
            ->willReturn(false);

        $builder2 = $this->createMock(SessionBuilder::class);
        $builder2
            ->method('isApplicable')
            ->with($request)
            ->willReturn(false);

        $service = new RestSessionFactoryTester();
        $service->builders = [
            $builder1,
            $builder2
        ];

        $service->createSessionFromRequest($request, $resolver);
    }

    public function testGetSessionBuilders(): void
    {
        $mock = $this->getMockForAbstractClass(SessionBuilder::class);

        $service = new RestSessionFactoryTester([
            RestSessionFactoryTester::OPTION_BUILDERS => [
                $mock
            ]
        ]);

        $this->assertEquals([$mock], $service->getSessionBuilders());
    }

    public function testGetSessionBuildersWithInvalidBuilder(): void
    {
        $this->expectException(LogicException::class);

        $mock = $this->getMockForAbstractClass(SessionBuilder::class);

        $service = new RestSessionFactoryTester([
            RestSessionFactoryTester::OPTION_BUILDERS => [
                $mock,
                new stdClass()
            ]
        ]);

        // Вызов провоцирует проверку валидности билдера
        $service->getSessionBuilders();
    }

    /**
     * @dataProvider testIsRestControllerProvider
     */
    public function testIsRestController(string $class, bool $expected): void
    {
        $service = new RestSessionFactoryTester();

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('getControllerClass')
            ->willReturn($class);

        $this->assertEquals($expected, $service->isRestController($resolver));
    }

    /**
     * @doesNotPerformAssertions
     * @return array
     */
    public function testIsRestControllerProvider(): array
    {
        return [
            [TestRest::class, true],
            ['toto', false],
            [SubTestRest::class, true],
            [__CLASS__, false],
            [__CLASS__, false],
        ];
    }
}

class TestRest extends tao_actions_RestController
{
}

class SubTestRest extends TestRest
{
}

class RestSessionFactoryTester extends RestSessionFactory
{
    public function isRestController(Resolver $resolver): bool
    {
        return parent::isRestController($resolver);
    }

    public array $builders = [];
    public int $isSessionStarted = 0;

    public function getSessionBuilders(): array
    {
        if ($this->builders) {
            return $this->builders;
        }

        return parent::getSessionBuilders();
    }

    public function startSession($session): void
    {
        $this->isSessionStarted = 1;
    }
}
