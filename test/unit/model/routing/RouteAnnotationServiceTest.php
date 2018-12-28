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

    public function testValidateException()
    {
        self::assertTrue($this->service->hasAccess());
    }

    public function testValidateNotFound()
    {
        self::assertTrue($this->service->isNotFound($this->service->getAnnotation(RouteAnnotationExample::class, 'notFoundAnnotation')));
    }

    public function testValidatePassed()
    {
        self::assertTrue($this->service->hasAccess($this->service->getAnnotation(RouteAnnotationExample::class, 'withoutAnnotation')));
    }
}
