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
 * Copyright (c) 2018-2025 (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\routing;

use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\oatbox\log\logger\AdvancedLogger;
use oat\tao\model\routing\RouteAnnotationService;
use oat\tao\model\routing\AnnotationReaderService;

class RouteAnnotationServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private RouteAnnotationService $sut;

    protected function setUp(): void
    {
        $annotationReaderService = $this->createMock(AnnotationReaderService::class);
        $annotationReaderService
            ->method('getAnnotations')
            ->willReturnCallback(
                static function (string $className, string $methodName): ?array {
                    if ($className !== 'class') {
                        return null;
                    }

                    switch ($methodName) {
                        case 'notFoundAnnotation':
                            return [
                                'security' => [
                                    RouteAnnotationService::SECURITY_HIDE,
                                    RouteAnnotationService::SECURITY_ALLOW,
                                ],
                            ];
                        case 'requiresRightRead':
                            return [
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
                            ];
                        case 'withoutAnnotation':
                            return [];
                        default:
                            return null;
                    }
                }
            );

        $this->sut = new RouteAnnotationService();
        $this->sut->setServiceLocator(
            $this->getServiceManagerMock([
                AdvancedLogger::ACL_SERVICE_ID => $this->createMock(AdvancedLogger::class),
                AnnotationReaderService::SERVICE_ID => $annotationReaderService,
            ])
        );
    }

    public function testIncorrectClassName()
    {
        self::assertFalse($this->sut->hasAccess('', ''));
    }

    public function testIsHidden()
    {
        self::assertTrue($this->sut->isHidden('class', 'notFoundAnnotation'));
    }

    public function testValidatePassed()
    {
        self::assertTrue($this->sut->hasAccess('class', 'withoutAnnotation'));
    }

    public function testHasAccessHidden()
    {
        self::assertFalse($this->sut->hasAccess('class', 'notFoundAnnotation'));
    }

    public function testHasAccessRights()
    {
        self::assertTrue($this->sut->hasAccess('class', 'requiresRightRead'));
    }
}
