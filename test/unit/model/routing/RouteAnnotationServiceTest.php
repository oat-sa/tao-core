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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
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
