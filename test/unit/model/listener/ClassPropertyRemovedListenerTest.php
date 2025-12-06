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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\listener;

use core_kernel_classes_Class;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\event\ClassPropertyRemovedEvent;
use oat\tao\model\listener\ClassPropertyRemovedListener;
use oat\tao\model\search\tasks\DeleteIndexProperty;
use oat\tao\model\taskQueue\QueueDispatcherInterface;

class ClassPropertyRemovedListenerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ClassPropertyRemovedListener $sut;
    private QueueDispatcherInterface|MockObject $queueDispatcher;
    private AdvancedSearchChecker|MockObject $advancedSearchChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ClassPropertyRemovedListener();

        $this->queueDispatcher = $this->createMock(QueueDispatcherInterface::class);
        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);

        $this->sut->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    QueueDispatcherInterface::SERVICE_ID => $this->queueDispatcher,
                    AdvancedSearchChecker::class => $this->advancedSearchChecker,
                ]
            )
        );
    }

    public function testRemoveClassProperty(): void
    {
        $this->advancedSearchChecker->method('isEnabled')->willReturn(true);

        $class = $this->createMock(core_kernel_classes_Class::class);

        $this->queueDispatcher->expects($this->once())
            ->method('createTask')
            ->with(
                new DeleteIndexProperty(),
                [
                    $class,
                    'property-name'
                ],
                'Updating search index',
                null,
                false
            );

        $this->sut->handleEvent(new ClassPropertyRemovedEvent($class, 'property-name'));
    }

    public function testRemoveClassPropertyNonQueued(): void
    {
        $this->advancedSearchChecker->method('isEnabled')->willReturn(false);

        $class = $this->createMock(core_kernel_classes_Class::class);

        $this->queueDispatcher->expects($this->never())
            ->method('createTask');

        $this->sut->handleEvent(new ClassPropertyRemovedEvent($class, 'property-name'));
    }
}
