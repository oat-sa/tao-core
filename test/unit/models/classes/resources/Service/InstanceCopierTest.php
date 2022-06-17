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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use RuntimeException;
use core_kernel_classes_Class;
use PHPUnit\Framework\TestCase;
use core_kernel_classes_Resource;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\resources\Service\InstanceCopier;
use oat\tao\model\resources\Contract\InstanceContentCopierInterface;
use oat\tao\model\resources\Contract\InstanceMetadataCopierInterface;

class InstanceCopierTest extends TestCase
{
    /** @var InstanceCopier */
    private $sut;

    /** @var InstanceMetadataCopierInterface|MockObject */
    private $instanceMetadataCopier;

    /** @var InstanceContentCopierInterface|MockObject */
    private $instanceContentCopier;

    protected function setUp(): void
    {
        $this->instanceMetadataCopier = $this->createMock(InstanceMetadataCopierInterface::class);
        $this->instanceContentCopier = $this->createMock(InstanceContentCopierInterface::class);

        $this->sut = new InstanceCopier($this->instanceMetadataCopier);
    }

    public function testCopy(): void
    {
        $instance = $this->createMock(core_kernel_classes_Resource::class);
        $instance
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('instanceLabel');

        $newInstance = $this->createMock(core_kernel_classes_Resource::class);

        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $destinationClass
            ->expects($this->once())
            ->method('createInstance')
            ->with('instanceLabel')
            ->willReturn($newInstance);

        $this->instanceMetadataCopier
            ->expects($this->once())
            ->method('copy')
            ->with($instance, $newInstance);

        $this->instanceContentCopier
            ->expects($this->never())
            ->method('copy');

        $this->assertEquals($newInstance, $this->sut->copy($instance, $destinationClass));
    }

    public function testCopyInstanceNotCreated(): void
    {
        $instance = $this->createMock(core_kernel_classes_Resource::class);
        $instance
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('instanceLabel');
        $instance
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('instanceUri');

        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $destinationClass
            ->expects($this->once())
            ->method('createInstance')
            ->with('instanceLabel')
            ->willReturn(null);
        $destinationClass
            ->expects($this->once())
            ->method('getUri')
            ->willReturn('destinationClassUri');

        $this->instanceMetadataCopier
            ->expects($this->never())
            ->method('copy');

        $this->instanceContentCopier
            ->expects($this->never())
            ->method('copy');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'New instance was not created. Original instance uri: instanceUri, destination class uri: destinationClassUri'
        );

        $this->sut->copy($instance, $destinationClass);
    }

    public function testCopyWithInstanceContentCopier(): void
    {
        $instance = $this->createMock(core_kernel_classes_Resource::class);
        $instance
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('instanceLabel');

        $newInstance = $this->createMock(core_kernel_classes_Resource::class);

        $destinationClass = $this->createMock(core_kernel_classes_Class::class);
        $destinationClass
            ->expects($this->once())
            ->method('createInstance')
            ->with('instanceLabel')
            ->willReturn($newInstance);

        $this->instanceMetadataCopier
            ->expects($this->once())
            ->method('copy')
            ->with($instance, $newInstance);

        $this->instanceContentCopier
            ->expects($this->once())
            ->method('copy')
            ->with($instance, $newInstance);

        $this->sut->withInstanceContentCopier($this->instanceContentCopier);

        $this->assertEquals($newInstance, $this->sut->copy($instance, $destinationClass));
    }
}
