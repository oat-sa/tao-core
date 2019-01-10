<?php
/**
 * Created by PhpStorm.
 * User: zagovorychev
 * Date: 2019-01-10
 * Time: 17:27
 */

namespace oat\test\model;

use oat\tao\model\routing\ControllerService;
use oat\tao\model\routing\RouteAnnotation;
use oat\tao\model\routing\RouteAnnotationService;
use oat\tao\test\unit\model\routing\sample\RouteAnnotationExample;
use Prophecy\Argument;
use Zend\ServiceManager\ServiceLocatorInterface;


abstract class AbsCl {}

/**
 * Class BlCl
 * @package oat\test\model
 * @RouteAnnotation("hidden")
 */
class BlCl {}

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
        $routeAnnotationServiceMock->isHidden(Argument::type('string'), Argument::type('string'))
            ->will(function ($args) {
                if ($args[0] === BlCl::class) {
                    return true;
                }
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

    public function testGetController()
    {
        self::assertInstanceOf(RouteAnnotationExample::class,
            $this->service->getController(RouteAnnotationExample::class));
    }

    /**
     * @expectedException \oat\tao\model\routing\RouterException
     * @expectedExceptionMessage The method "protectedAction" is not public in the class "oat\tao\test\unit\model\routing\sample\RouteAnnotationExample"
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
}
