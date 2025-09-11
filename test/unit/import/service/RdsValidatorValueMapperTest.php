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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\test\unit\import\service;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\tao\model\import\service\RdsResourceNotFoundException;
use oat\tao\model\import\service\RdsValidatorValueMapper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RdsValidatorValueMapperTest extends TestCase
{
    public function testMap()
    {
        $service = $this->getService(null, $this->mockResource(true), []);

        $this->assertInstanceOf(core_kernel_classes_Resource::class, $service->map('someValue'));
    }

    public function testMapNoResourceFound()
    {
        $this->expectException(RdsResourceNotFoundException::class);
        $service = $this->getService('someClass', null, []);
        $service->map('someValue');
    }

    public function testMapMultipleResourceFound()
    {
        $this->expectException(RdsResourceNotFoundException::class);
        $service = $this->getService('someClass', null, [$this->mockResource(true), $this->mockResource(true)]);
        $service->map('someValue');
    }

    public function testMapMultipleResourceNotAsClass()
    {
        $this->expectException(RdsResourceNotFoundException::class);
        $service = $this->getService(null, $this->mockResource(false), []);
        $service->map('someValue');
    }

    /**
     * @param $retProperty
     * @param $resource
     * @param array $searchInstances
     * @return RdsValidatorValueMapper
     */
    protected function getService($retProperty, $resource, $searchInstances = [])
    {
        $service = $this->getMockBuilder(RdsValidatorValueMapper::class)->disableOriginalConstructor()
            ->onlyMethods(['getOption','getClass'])->getMockForAbstractClass();

        $classMock = $this->getMockBuilder(core_kernel_classes_Class::class)->disableOriginalConstructor()->getMock();
        $classMock
            ->method('getResource')
            ->willReturn($resource);
        $classMock
            ->method('searchInstances')
            ->willReturn($searchInstances);

        $service
            ->method('getClass')
            ->willReturn($classMock);

        $service
            ->method('getOption')
            ->willReturnCallback(function ($param) use ($retProperty) {
                if ($param === RdsValidatorValueMapper::OPTION_PROPERTY) {
                    return $retProperty;
                }

                return $param;
            });

        return $service;
    }

    /**
     * @param $instanceOf
     * @return MockObject
     */
    protected function mockResource($instanceOf)
    {
        $mock = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->onlyMethods(['isInstanceOf'])
            ->disableOriginalConstructor()->getMock();

        $mock->method('isInstanceOf')->willReturn($instanceOf);

        return $mock;
    }
}
