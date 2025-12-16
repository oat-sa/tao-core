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
 */

declare(strict_types=1);

namespace oat\test\unit\model\listener;

use core_kernel_classes_Property;
use PHPUnit\Framework\MockObject\MockObject;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\dto\OldProperty;
use oat\tao\model\event\ClassPropertiesChangedEvent;
use oat\tao\model\listener\ClassPropertiesChangedListener;
use oat\tao\model\search\tasks\RenameIndexProperties;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use RuntimeException;

class ClassPropertiesChangedListenerTest extends TestCase
{
    use ServiceManagerMockTrait;

    private ClassPropertiesChangedListener $sut;
    private QueueDispatcherInterface|MockObject $queueDispatcher;
    private AdvancedSearchChecker|MockObject $advancedSearchChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ClassPropertiesChangedListener();

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

    public function testRenameClassProperties(): void
    {
        $this->advancedSearchChecker->method('isEnabled')->willReturn(true);
        $this->queueDispatcher->expects($this->once())
            ->method('createTask')
            ->with(
                new RenameIndexProperties(),
                [
                    [
                        'uri' => null,
                        'oldLabel' => 'test',
                        'oldAlias' => 'old alias',
                        'oldPropertyType' => null,
                    ]
                ],
                'Updating search index',
                null,
                false
            );

        $this->sut->handleEvent(
            new ClassPropertiesChangedEvent(
                [
                    [
                        'oldProperty' => new OldProperty('test', null, null, [], null, 'old alias'),
                        'property' => $this->createMock(core_kernel_classes_Property::class)
                    ]
                ]
            )
        );
    }

    public function testRenameClassPropertiesNonQueued(): void
    {
        $this->advancedSearchChecker->method('isEnabled')->willReturn(false);
        $this->queueDispatcher->expects($this->never())
            ->method('createTask');

        $this->sut->handleEvent(
            new ClassPropertiesChangedEvent(
                [
                    [
                        'oldProperty' => new OldProperty('test', null),
                        'property' => $this->createMock(core_kernel_classes_Property::class)
                    ]
                ]
            )
        );
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testExceptionWhenCallingRenameClassProperties(array $property): void
    {
        $this->advancedSearchChecker->method('isEnabled')->willReturn(true);
        $this->expectException(RuntimeException::class);

        $this->queueDispatcher->expects($this->never())
            ->method('createTask');

        $this->sut->handleEvent(
            new ClassPropertiesChangedEvent(
                [
                    $property
                ]
            )
        );
    }

    public function provideInvalidData(): array
    {
        return [
            'with no old property' => [
                'properties' => [
                    'property' => $this->createMock(core_kernel_classes_Property::class)
                ]
            ],
            'with no property' => [
                'properties' => [
                    'oldProperty' => new OldProperty('test', null)
                ]
            ],
        ];
    }
}
