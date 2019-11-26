<?php

declare(strict_types=1);

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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\session;

use oat\generis\test\TestCase;
use oat\tao\model\routing\Resolver;
use oat\tao\model\session\restSessionFactory\RestSessionFactory;
use oat\tao\model\session\restSessionFactory\SessionBuilder;

class RestSessionFactoryTest extends TestCase
{
    public function testCreateSessionFromRequest(): void
    {
        $class = TestRest::class;
        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->getControllerClass()->willReturn($class);

        $request = new \common_http_Request('http:://fixture.test');

        $sessionBuilderProphecy = $this->prophesize(SessionBuilder::class);
        $sessionBuilderProphecy->isApplicable($request)->willReturn(false);

        $sessionBuilderProphecy2 = $this->prophesize(SessionBuilder::class);
        $sessionBuilderProphecy2->isApplicable($request)->willReturn(true);
        $sessionBuilderProphecy2->getSession($request)->shouldBeCalled();

        $service = new RestSessionFactoryTester();
        $service->builders = [
            $sessionBuilderProphecy->reveal(),
            $sessionBuilderProphecy2->reveal(),
        ];

        $service->createSessionFromRequest($request, $resolverProphecy->reveal());
        $this->assertSame(1, $service->isSessionStarted);
    }

    public function testCreateSessionFromRequestWithInalidRestController(): void
    {
        $class = \stdClass::class;
        $resolverMock = $this->prophesize(Resolver::class);
        $resolverMock->getControllerClass()->willReturn($class);

        $request = new \common_http_Request('http:://fixture.test');

        $service = new RestSessionFactoryTester();
        $this->assertFalse($service->createSessionFromRequest($request, $resolverMock->reveal()));
    }

    /**
     * @expectedException \common_Exception
     */
    public function testCreateSessionFromRequestWithNoBuilders(): void
    {
        $class = TestRest::class;
        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->getControllerClass()->willReturn($class);

        $request = new \common_http_Request('http:://fixture.test');

        $service = new RestSessionFactoryTester([
            RestSessionFactoryTester::OPTION_BUILDERS => [],
        ]);

        $service->createSessionFromRequest($request, $resolverProphecy->reveal());
    }

    /**
     * @expectedException \common_Exception
     */
    public function testCreateSessionFromRequestWithNoApplicableBuilders(): void
    {
        $class = TestRest::class;
        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->getControllerClass()->willReturn($class);

        $request = new \common_http_Request('http:://fixture.test');

        $mock = $this->prophesize(SessionBuilder::class);
        $mock->isApplicable($request)->willReturn(false);

        $mock2 = $this->prophesize(SessionBuilder::class);
        $mock2->isApplicable($request)->willReturn(false);

        $service = new RestSessionFactoryTester();
        $service->builders = [
            $mock->reveal(),
            $mock2->reveal(),
        ];

        $service->createSessionFromRequest($request, $resolverProphecy->reveal());
    }

    public function testGetSessionBuilders(): void
    {
        $mock = $this->getMockForAbstractClass(SessionBuilder::class);

        $service = new RestSessionFactoryTester([
            RestSessionFactoryTester::OPTION_BUILDERS => [
                $mock,
            ],
        ]);

        $this->assertSame([$mock], $service->getSessionBuilders());
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetSessionBuildersWithInvalidBuilder(): void
    {
        $mock = $this->getMockForAbstractClass(SessionBuilder::class);

        $service = new RestSessionFactoryTester([
            RestSessionFactoryTester::OPTION_BUILDERS => [
                $mock,
                new \stdClass(),
            ],
        ]);

        $this->assertSame([$mock], $service->getSessionBuilders());
    }

    /**
     * @dataProvider testIsRestControllerProvider
     * @param $class
     * @param $expected
     */
    public function testIsRestController($class, $expected): void
    {
        $service = new RestSessionFactoryTester();

        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->getControllerClass()->willReturn($class);

        $this->assertSame($expected, $service->isRestController($resolverProphecy->reveal()));
    }

    /**
     * Provider of testIsRestController
     *
     * @return array
     */
    public function testIsRestControllerProvider()
    {
        return [
            [TestRest::class, true],
            ['toto', false],
            [SubTestRest::class, true],
            [self::class, false],
            [self::class, false],
        ];
    }
}

class TestRest extends \tao_actions_RestController
{
}
class SubTestRest extends TestRest
{
}

class RestSessionFactoryTester extends RestSessionFactory
{
    public $builders;

    public $isSessionStarted = 0;

    public function isRestController(Resolver $resolver)
    {
        return parent::isRestController($resolver);
    }

    public function getSessionBuilders()
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
