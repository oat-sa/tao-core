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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\test\model;

use oat\tao\model\routing\ControllerService;
use oat\tao\model\routing\RouteAnnotationService;
use oat\tao\model\routing\RouterException;
use Prophecy\Argument;
use Zend\ServiceManager\ServiceLocatorInterface;
use oat\generis\test\TestCase;

abstract class AbsCl
{
}

/**
 * Class BlCl
 * @package oat\test\model
 */
class BlCl
{
}

class RouteAnnotationExample
{

    protected function protectedAction()
    {
    }
    public function notFoundAnnotation()
    {
    }
    public function withoutAnnotation()
    {
    }
}

class ControllerServiceTest extends TestCase
{

    /**
     * @var ControllerService
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new ControllerService();

        $routeAnnotationServiceMock = $this->prophesize(RouteAnnotationService::class);
        $routeAnnotationServiceMock->hasAccess(Argument::type('string'), Argument::type('string'))
            ->will(function ($args) {
                if ($args[0] === BlCl::class) {
                    return false;
                }
                return true;
            });
        $routeAnnotationService = $routeAnnotationServiceMock->reveal();

        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $this->prophesize(ServiceLocatorInterface::class);
        $serviceLocator->get(Argument::type('string'))->will(function ($args) use ($routeAnnotationService) {
            if ($args[0] === RouteAnnotationService::SERVICE_ID) {
                return $routeAnnotationService;
            }
        });
        $this->service->setServiceLocator($serviceLocator->reveal());
    }

    public function testGetControllerAbstractClass()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Attempt to run an action from the Abstract class "oat\test\model\AbsCl"');
        $this->service->checkController(AbsCl::class);
    }

    public function testGetControllerBlockedByAnnotation()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Class \'oat\test\model\BlCl\' blocked by route annotation');
        $this->service->checkController(BlCl::class);
    }

    /**
     * @throws \oat\tao\model\routing\RouterException
     */
    public function testGetController()
    {
        $this->assertEquals(
            RouteAnnotationExample::class,
            $this->service->checkController(RouteAnnotationExample::class)
        );
    }

    public function testGetNonPublicAction()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('The method "protectedAction" is not public in the class "oat\test\model\RouteAnnotationExample"');
        $this->service->getAction(RouteAnnotationExample::class, 'protectedAction');
    }

    /**
     * @throws RouterException
     * @doesNotPerformAssertions
     */
    public function testGetActionBlockedByAnnotation()
    {
        $this->service->getAction(RouteAnnotationExample::class, 'notFoundAnnotation');
    }

    /**
     * @throws RouterException
     * @doesNotPerformAssertions
     */
    public function testGetAction()
    {
        $this->service->getAction(RouteAnnotationExample::class, 'withoutAnnotation');
    }

    public function testGetNonexistentAction()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Method oat\test\model\RouteAnnotationExample::methodNotExists() does not exist');
        $this->service->getAction(RouteAnnotationExample::class, 'methodNotExists');
    }
}
