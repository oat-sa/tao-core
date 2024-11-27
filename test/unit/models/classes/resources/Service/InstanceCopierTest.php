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
 * Copyright (c) 2022-2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\resources\Service;

use oat\generis\model\data\Ontology;
use oat\oatbox\event\EventManager;
use oat\tao\model\resources\Command\ResourceTransferCommand;
use oat\tao\model\resources\Event\InstanceCopiedEvent;
use oat\tao\model\resources\ResourceTransferResult;
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
    private InstanceCopier $sut;

    /** @var InstanceMetadataCopierInterface|MockObject */
    private $instanceMetadataCopier;

    /** @var InstanceContentCopierInterface|MockObject */
    private $instanceContentCopier;

    /** @var Ontology|MockObject */
    private $ontology;

    /** @var EventManager|MockObject */
    private $eventManager;

    protected function setUp(): void
    {
        $this->instanceMetadataCopier = $this->createMock(InstanceMetadataCopierInterface::class);
        $this->instanceContentCopier = $this->createMock(InstanceContentCopierInterface::class);
        $this->ontology = $this->createMock(Ontology::class);
        $this->eventManager = $this->createMock(EventManager::class);

        $this->sut = new InstanceCopier($this->instanceMetadataCopier, $this->ontology);
        $this->sut->withEventManager($this->eventManager);
    }

    public function testTransfer(): void
    {
        $instance = $this->createInstance('instanceLabel', 'instanceUri');
        $newInstance = $this->createInstance('instanceLabel', 'newInstanceUri');
        $destinationClass = $this->createClass('destinationClassUri', $newInstance);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->willReturn($instance);

        $this->ontology
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($destinationClass);

        $this->instanceMetadataCopier
            ->expects($this->once())
            ->method('copy')
            ->with($instance, $newInstance);

        $this->instanceContentCopier
            ->expects($this->never())
            ->method('copy');

        $this->eventManager
            ->expects($this->once())
            ->method('trigger')
            ->with(new InstanceCopiedEvent('newInstanceUri'));

        $this->assertEquals(
            new ResourceTransferResult(
                'newInstanceUri'
            ),
            $this->sut->transfer(
                new ResourceTransferCommand(
                    'instanceUri',
                    'destinationClassUri',
                    ResourceTransferCommand::ACL_KEEP_ORIGINAL,
                    ResourceTransferCommand::TRANSFER_MODE_COPY
                )
            )
        );
    }

    public function testCopy(): void
    {
        $instance = $this->createInstance('instanceLabel', 'instanceUri');
        $newInstance = $this->createMock(core_kernel_classes_Resource::class);
        $destinationClass = $this->createClass('destinationClassUri', $newInstance);

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
        $instance = $this->createInstance('instanceLabel', 'instanceUri');
        $destinationClass = $this->createClass('destinationClassUri', null);

        $this->instanceMetadataCopier
            ->expects($this->never())
            ->method('copy');

        $this->instanceContentCopier
            ->expects($this->never())
            ->method('copy');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'New instance was not created. Original instance uri: instanceUri, ' .
            'destination class uri: destinationClassUri'
        );

        $this->sut->copy($instance, $destinationClass);
    }

    public function testCopyWithInstanceContentCopier(): void
    {
        $instance = $this->createInstance('instanceLabel', 'uri');
        $newInstance = $this->createMock(core_kernel_classes_Resource::class);
        $destinationClass = $this->createClass('destinationClassUri', $newInstance);

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

    private function createClass(string $uri, ?core_kernel_classes_Resource $newInstance): core_kernel_classes_Class
    {
        $class = $this->createMock(core_kernel_classes_Class::class);
        $class->method('createInstance')
            ->with('instanceLabel')
            ->willReturn($newInstance);

        $class->method('getUri')
            ->willReturn($uri);

        return $class;
    }

    private function createInstance(string $label, string $uri): core_kernel_classes_Resource
    {
        $instance = $this->createMock(core_kernel_classes_Resource::class);

        $instance->method('getLabel')
            ->willReturn($label);

        $instance->method('getUri')
            ->willReturn($uri);

        return $instance;
    }
}
