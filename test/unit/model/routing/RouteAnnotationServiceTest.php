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
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class RouteAnnotationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouteAnnotationService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();
        $cacheService = $this->prophesize(\common_cache_Cache::class);
        $cacheService->get(Argument::type('string'))->willThrow(new \common_cache_NotFoundException('PhpUnit exception'));
        $cacheService->put(Argument::any(), Argument::any())->willReturn(true);
        $this->service = new RouteAnnotationService([
            'cacheService' => $cacheService->reveal()]
        );
        $logger = $this->prophesize(LoggerInterface::class);
        $this->service->setLogger($logger->reveal());
    }

    public function testIncorrectClassName()
    {
        self::assertFalse($this->service->hasAccess('', ''));
    }

    public function testIsHidden()
    {
        self::assertTrue($this->service->isHidden(RouteAnnotationExample::class, 'notFoundAnnotation'));
    }

    public function testValidatePassed()
    {
        self::assertTrue($this->service->hasAccess(RouteAnnotationExample::class, 'withoutAnnotation'));
    }

    public function testHasAccessHidden()
    {
        self::assertFalse($this->service->hasAccess(RouteAnnotationExample::class, 'notFoundAnnotation'));
    }

    public function testHasAccessRights()
    {
        self::assertTrue($this->service->hasAccess(RouteAnnotationExample::class, 'requiresRightRead'));
    }
}
