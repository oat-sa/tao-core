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
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\routing;

use common_cache_Cache;
use common_cache_NotFoundException;
use oat\generis\test\ServiceManagerMockTrait;
use oat\tao\model\routing\AnnotationReader\requiredRights;
use oat\tao\model\routing\AnnotationReader\security;
use oat\tao\model\routing\AnnotationReaderService;
use oat\tao\model\routing\RouteAnnotationService;
use PHPUnit\Framework\TestCase;

/**
 * @requiredRights(key="id", permission="READ")
 * @security("allow")
 */
class TestingClass
{
    /**
     * @requiredRights(key="id", permission="READ")
     * @requiredRights(key="uri", permission="WRITE")
     * @security("hide")
     * @security("allow")
     */
    public function someAction()
    {
    }

    public function anotherAction()
    {
    }
}


class AnnotationReaderServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private AnnotationReaderService $service;

    protected function setUp(): void
    {
        $this->service = new AnnotationReaderService();

        $cacheService = $this->createMock(common_cache_Cache::class);
        $cacheService
            ->method('has')
            ->with($this->isType('string'))
            ->willReturn(false);
        $cacheService
            ->method('get')
            ->with($this->isType('string'))
            ->willThrowException(new common_cache_NotFoundException('PhpUnit exception'));
        $cacheService
            ->method('put')
            ->willReturn(true);

        $serviceManagerMock = $this->getServiceManagerMock([
            common_cache_Cache::SERVICE_ID => $cacheService,
        ]);

        $this->service->setServiceLocator($serviceManagerMock);
    }

    public function testGetAnnotations(): void
    {
        $annotations = $this->service->getAnnotations(TestingClass::class, 'someAction');
        self::assertSame(
            [
                'required_rights' => [
                    [
                        'key' => 'id',
                        'permission' => 'READ',
                    ],
                    [
                        'key' => 'uri',
                        'permission' => 'WRITE',
                    ],
                ],
                'security' => [RouteAnnotationService::SECURITY_HIDE, RouteAnnotationService::SECURITY_ALLOW],
            ],
            $annotations
        );
    }

    public function testGetBlankAnnotations(): void
    {
        $annotations = $this->service->getAnnotations(TestingClass::class, 'anotherAction');
        self::assertSame(
            [
                'required_rights' => [],
                'security' => [],
            ],
            $annotations
        );
    }

    public function testGetClassAnnotations(): void
    {
        $annotations = $this->service->getAnnotations(TestingClass::class, '');
        self::assertSame(
            [
                'required_rights' => [[
                    'key' => 'id',
                    'permission' => 'READ',
                ]],
                'security' => ['allow'],
            ],
            $annotations
        );
    }
}
