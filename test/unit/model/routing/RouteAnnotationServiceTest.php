<?php
/**
 * Created by PhpStorm.
 * User: zagovorychev
 * Date: 2018-12-26
 * Time: 13:32
 */

namespace oat\tao\test\unit\model\routing;

use oat\tao\model\routing\RouteAnnotationService;
use oat\tao\test\unit\model\routing\sample\RouteAnnotationExample;

class RouteAnnotationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouteAnnotationService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new RouteAnnotationService();
    }

    /**
     * @expectedException  \ReflectionException
     */
    public function testValidateException()
    {
        $this->service->validate('');
    }

    /**
     * @expectedException \ResolverException
     * @expectedExceptionMessage Blocked by the method annotation
     */
    public function testValidateNotFound()
    {
        $this->service->validate(RouteAnnotationExample::class, 'notFoundAnnotation');
    }
}
