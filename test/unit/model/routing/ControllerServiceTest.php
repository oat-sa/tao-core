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
use Prophecy\Argument;
use Zend\ServiceManager\ServiceLocatorInterface;


abstract class AbsCl {}

/**
 * Class BlCl
 * @package oat\test\model
 * @RouteAnnotation("hidden")
 */
class BlCl {}

class RouteAnnotationExample {

    protected function protectedAction(){}
    public function notFoundAnnotation(){}
    public function withoutAnnotation(){}
}

class ControllerServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ControllerService
     */
    private $service;

    public function setUp()
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

    /**
     * @expectedException \oat\tao\model\routing\RouterException
     * @expectedExceptionMessage Attempt to run an action from the Abstract class "oat\test\model\AbsCl"
     */
    public function testGetControllerAbstractClass()
    {
        $this->service->getController(AbsCl::class);
    }

    /**
     * @expectedException \oat\tao\model\routing\RouterException
     * @expectedExceptionMessage Class 'oat\test\model\BlCl' blocked by route annotation
     */
    public function testGetControllerBlockedByAnnotation()
    {
        $this->service->getController(BlCl::class);
    }

    /**
     * @throws \oat\tao\model\routing\RouterException
     */
    public function testGetController()
    {
        self::assertInstanceOf(RouteAnnotationExample::class,
            $this->service->getController(RouteAnnotationExample::class));
    }

    /**
     * @expectedException \oat\tao\model\routing\RouterException
     * @expectedExceptionMessage The method "protectedAction" is not public in the class "oat\test\model\RouteAnnotationExample"
     */
    public function testGetNonPublicAction()
    {
        $this->service->getAction(RouteAnnotationExample::class, 'protectedAction');
    }

    public function testGetActionBlockedByAnnotation()
    {
        $this->service->getAction(RouteAnnotationExample::class, 'notFoundAnnotation');
    }

    public function testGetAction()
    {
        $this->service->getAction(RouteAnnotationExample::class, 'withoutAnnotation');
    }

    /**
     * @expectedException \oat\tao\model\routing\RouterException
     * @expectedExceptionMessage Method oat\test\model\RouteAnnotationExample::methodNotExists() does not exist
     */
    public function testGetNonexistentAction()
    {
        $this->service->getAction(RouteAnnotationExample::class, 'methodNotExists');
    }
}
