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
 * Copyright (c) 2019-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\routing;

use oat\generis\test\ServiceManagerMockTrait;
use oat\tao\model\routing\ControllerService;
use oat\tao\model\routing\RouteAnnotationService;
use oat\tao\model\routing\RouterException;
use PHPUnit\Framework\TestCase;

abstract class AbsCl
{
}

class BlCl
{
}

class RouteAnnotationExample
{
    protected function protectedAction(): void
    {
    }

    public function notFoundAnnotation(): void
    {
    }

    public function withoutAnnotation(): void
    {
    }
}

class ControllerServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ControllerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ControllerService();

        // RouteAnnotationService mock
        $routeAnnotationService = $this->createMock(RouteAnnotationService::class);
        $routeAnnotationService
            ->method('hasAccess')
            ->willReturnCallback(function (string $class, string $method) {
                return $class !== BlCl::class;
            });

        $serviceManagerMock = $this->getServiceManagerMock([
            RouteAnnotationService::SERVICE_ID => $routeAnnotationService,
        ]);

        $this->service->setServiceLocator($serviceManagerMock);
    }

    public function testGetControllerAbstractClass(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage(
            'Attempt to run an action from the Abstract class "oat\tao\test\unit\model\routing\AbsCl"'
        );
        $this->service->checkController(AbsCl::class);
    }

    public function testGetControllerBlockedByAnnotation(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Class \'oat\tao\test\unit\model\routing\BlCl\' blocked by route annotation');
        $this->service->checkController(BlCl::class);
    }

    /**
     * @throws RouterException
     */
    public function testGetController(): void
    {
        $this->assertEquals(
            RouteAnnotationExample::class,
            $this->service->checkController(RouteAnnotationExample::class)
        );
    }

    public function testGetNonPublicAction(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage(
            'The method "protectedAction" is not public in the class '
            . '"oat\tao\test\unit\model\routing\RouteAnnotationExample"'
        );
        $this->service->getAction(RouteAnnotationExample::class, 'protectedAction');
    }

    /**
     * @throws RouterException
     * @doesNotPerformAssertions
     */
    public function testGetActionBlockedByAnnotation(): void
    {
        $this->service->getAction(RouteAnnotationExample::class, 'notFoundAnnotation');
    }

    /**
     * @throws RouterException
     * @doesNotPerformAssertions
     */
    public function testGetAction(): void
    {
        $this->service->getAction(RouteAnnotationExample::class, 'withoutAnnotation');
    }

    public function testGetNonexistentAction(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage(
            'Method oat\tao\test\unit\model\routing\RouteAnnotationExample::methodNotExists() does not exist'
        );
        $this->service->getAction(RouteAnnotationExample::class, 'methodNotExists');
    }
}
