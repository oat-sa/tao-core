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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\test\unit\session;

use oat\tao\model\routing\Resolver;
use oat\tao\model\session\restSessionFactory\RestSessionFactory;
use oat\tao\model\session\restSessionFactory\SessionBuilder;

class RestSessionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSessionFromRequest()
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
        $service->builders = array(
            $sessionBuilderProphecy->reveal(),
            $sessionBuilderProphecy2->reveal()
        );

        $service->createSessionFromRequest($request, $resolverProphecy->reveal());
        $this->assertEquals(1, $service->isSessionStarted);
    }

    public function testCreateSessionFromRequestWithInalidRestController()
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
    public function testCreateSessionFromRequestWithNoBuilders()
    {
        $class = TestRest::class;
        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->getControllerClass()->willReturn($class);

        $request = new \common_http_Request('http:://fixture.test');

        $service = new RestSessionFactoryTester(array(
            RestSessionFactoryTester::OPTION_BUILDERS => array()
        ));

        $service->createSessionFromRequest($request, $resolverProphecy->reveal());
    }

    /**
     * @expectedException \common_Exception
     */
    public function testCreateSessionFromRequestWithNoApplicableBuilders()
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
        $service->builders = array(
            $mock->reveal(),
            $mock2->reveal()
        );

        $service->createSessionFromRequest($request, $resolverProphecy->reveal());
    }

    public function testGetSessionBuilders()
    {
        $mock = $this->getMockForAbstractClass(SessionBuilder::class);

        $service = new RestSessionFactoryTester(array(
            RestSessionFactoryTester::OPTION_BUILDERS => array(
                $mock
            )
        ));

        $this->assertEquals([$mock], $service->getSessionBuilders());
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetSessionBuildersWithInvalidBuilder()
    {
        $mock = $this->getMockForAbstractClass(SessionBuilder::class);

        $service = new RestSessionFactoryTester(array(
            RestSessionFactoryTester::OPTION_BUILDERS => array(
                $mock,
                new \stdClass()
            )
        ));

        $this->assertEquals([$mock], $service->getSessionBuilders());
    }

    /**
     * @dataProvider testIsRestControllerProvider
     * @param $class
     * @param $expected
     */
    public function testIsRestController($class, $expected)
    {
        $service = new RestSessionFactoryTester();

        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->getControllerClass()->willReturn($class);

        $this->assertEquals($expected, $service->isRestController($resolverProphecy->reveal()));
    }

    /**
     * Provider of testIsRestController
     *
     * @return array
     */
    public function testIsRestControllerProvider()
    {
        return [
            [TestRest::class, true,],
            ['toto', false],
            [SubTestRest::class, true,],
            [RestSessionFactoryTest::class, false,],
            [RestSessionFactoryTest::class, false,],
        ];
    }
}

class TestRest extends \tao_actions_RestController {}
class SubTestRest extends TestRest {}

class RestSessionFactoryTester extends RestSessionFactory
{
    public function isRestController(Resolver $resolver)
    {
        return parent::isRestController($resolver);
    }

    public $builders;
    public $isSessionStarted = 0;

    public function getSessionBuilders()
    {
        if ($this->builders) {
            return $this->builders;
        }
        return parent::getSessionBuilders();
    }

    public function startSession($session)
    {
        $this->isSessionStarted = 1;
    }

}