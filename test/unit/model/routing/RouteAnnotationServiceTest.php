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

use oat\generis\test\TestCase;
use oat\tao\model\routing\AnnotationReaderService;
use oat\tao\model\routing\RouteAnnotationService;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RouteAnnotationServiceTest extends TestCase
{
    /**
     * @var RouteAnnotationService
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RouteAnnotationService();
        $logger = $this->prophesize(LoggerInterface::class);
        $this->service->setLogger($logger->reveal());
        $annotationReaderService = $this->prophesize(AnnotationReaderService::class);
        $annotationReaderService->getAnnotations(Argument::type('string'), Argument::type('string'))->will(function ($args) {
            if ($args[0] === 'class') {
                switch ($args[1]) {
                    case 'notFoundAnnotation':
                        return ['security' => [RouteAnnotationService::SECURITY_HIDE, RouteAnnotationService::SECURITY_ALLOW]];
                        break;
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
                        break;
                    case 'withoutAnnotation':
                        return [];
                        break;
                }
            }
        });

        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $this->prophesize(ServiceLocatorInterface::class);
        $serviceLocator->get(Argument::type('string'))->will(function ($args) use ($annotationReaderService) {
            if ($args[0] === AnnotationReaderService::SERVICE_ID) {
                return $annotationReaderService->reveal();
            }
        });
        $this->service->setServiceLocator($serviceLocator->reveal());
    }

    public function testIncorrectClassName()
    {
        self::assertFalse($this->service->hasAccess('', ''));
    }

    public function testIsHidden()
    {
        self::assertTrue($this->service->isHidden('class', 'notFoundAnnotation'));
    }

    public function testValidatePassed()
    {
        self::assertTrue($this->service->hasAccess('class', 'withoutAnnotation'));
    }

    public function testHasAccessHidden()
    {
        self::assertFalse($this->service->hasAccess('class', 'notFoundAnnotation'));
    }

    public function testHasAccessRights()
    {
        self::assertTrue($this->service->hasAccess('class', 'requiresRightRead'));
    }
}
