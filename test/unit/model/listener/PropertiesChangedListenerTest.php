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

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\dto\OldProperty;
use oat\tao\model\event\PropertiesChangedEvent;
use oat\tao\model\listener\PropertiesChangedListener;
use oat\tao\model\search\tasks\RenameIndexProperties;
use oat\tao\model\taskQueue\QueueDispatcherInterface;

class PropertiesChangedListenerTest extends TestCase
{
    /** @var ServiceManager|MockObject */
    private $serviceManager;

    /** @var QueueDispatcherInterface|MockObject */
    private $queueDispatcher;

    /** @var PropertiesChangedListener */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new PropertiesChangedListener();

        $this->serviceManager = $this->createMock(ServiceManager::class);

        $this->sut->setServiceLocator($this->serviceManager);

        $this->queueDispatcher = $this->createMock(QueueDispatcherInterface::class);

        $this->serviceManager->expects($this->any())
            ->method('get')
            ->with(QueueDispatcherInterface::SERVICE_ID)
            ->willReturn($this->queueDispatcher);

        ServiceManager::setServiceManager($this->serviceManager);
    }

    public function testCatchPropertiesChangedEvent(): void {
        $this->queueDispatcher->expects($this->once())
            ->method('createTask')
            ->with(
                new RenameIndexProperties(),
                [
                    [
                        'uri' => null,
                        'oldLabel' => 'test',
                        'oldPropertyType' => null,
                    ]
                ],
                'Updating Indexes',
                null,
                false
            );

        $this->sut->catchPropertiesChangedEvent(
            new PropertiesChangedEvent(
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
    public function testCatchPropertiesChangedEventCanReturnException(array $property): void {
        $this->expectException(RuntimeException::class);

        $this->queueDispatcher->expects($this->never())
            ->method('createTask');

        $this->sut->catchPropertiesChangedEvent(
            new PropertiesChangedEvent(
                [
                    $property
                ]
            )
        );
    }

    public function provideInvalidData(): array
    {
        return [
            'with no old propeerty' => [
                'properties' => [
                    'property' => $this->createMock(core_kernel_classes_Property::class)
                ]
            ],
            'with no propeerty' => [
                'properties' => [
                    'oldProperty' => new OldProperty('test', null)
                ]
            ],
        ];
    }
}
