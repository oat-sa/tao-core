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
use oat\tao\model\import\service\RdsValidatorValueMapper;

class RdsValidatorValueMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $service = $this->getService(null, $this->mockResource(true), []);

        $this->assertInstanceOf(core_kernel_classes_Resource::class, $service->map('someValue'));
    }

    /**
     * @expectedException  \oat\tao\model\import\service\RdsResourceNotFoundException
     */
    public function testMapNoResourceFound()
    {
        $service = $this->getService('someClass', null, []);
        $service->map('someValue');
    }

    /**
     * @expectedException  \oat\tao\model\import\service\RdsResourceNotFoundException
     */
    public function testMapMultipleResourceFound()
    {
        $service = $this->getService('someClass', null, [$this->mockResource(true), $this->mockResource(true)]);
        $service->map('someValue');
    }

    /**
     * @expectedException  \oat\tao\model\import\service\RdsResourceNotFoundException
     */
    public function testMapMultipleResourceNotAsClass()
    {
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
            ->setMethods(['getOption','getClass'])->getMockForAbstractClass();

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
            ->willReturnCallback(function ($param) use ($retProperty){
                if ($param === RdsValidatorValueMapper::OPTION_PROPERTY){
                    return $retProperty;
                }

                return $param;
            });

        return $service;
    }

    /**
     * @param $instanceOf
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockResource($instanceOf)
    {
        $mock = $this->getMockBuilder(core_kernel_classes_Resource::class)
            ->setMethods(['isInstanceOf'])
            ->disableOriginalConstructor()->getMock();

        $mock->method('isInstanceOf')->willReturn($instanceOf);

        return $mock;
    }
}
