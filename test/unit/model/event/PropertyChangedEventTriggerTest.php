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
 *
 */
declare(strict_types=1);

use oat\generis\model\WidgetRdf;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\OldProperty;
use oat\tao\model\event\PropertyChangedEvent;
use oat\tao\model\event\PropertyChangedEventTrigger;
use oat\generis\test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PropertyChangedEventTriggerTest extends TestCase
{
    /** @var PropertyChangedEventTrigger|MockObject */
    private $sut;

    /** @var MockObject|EventManager */
    private $eventManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventManager = $this->createMock(EventManager::class);
        $this->sut = new PropertyChangedEventTrigger($this->eventManager);
    }

    public function testDoNotTriggerIfDoesNotHaveChanges(): void{
        $this->eventManager->expects($this->never())->method('trigger');

        $property = $this->createPropertyMock();

        $this->sut->triggerIfNeeded(
            new PropertyChangedEvent(
                $property,
                new OldProperty('', $property)
            )
        );
    }

    public function testDoNotTriggerIfDoesNotHavePropertyType(): void{
        $this->eventManager->expects($this->never())->method('trigger');

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->expects($this->once())
            ->method('getOnePropertyValue')
            ->with(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET))
            ->willReturn(null);

        $this->sut->triggerIfNeeded(
            new PropertyChangedEvent(
                $property,
                new OldProperty('', null)
            )
        );
    }

    public function testTriggerIfHaveCurrentPropertyTypeButDoesNotHaveOldPropertyType(): void{
        $property = $this->createPropertyMock();
        $event =  new PropertyChangedEvent(
            $property,
            new OldProperty('', null)
        );

        $this->eventManager->expects($this->once())->method('trigger')->with($event);

        $this->sut->triggerIfNeeded(
            $event
        );
    }

    public function testTriggerIfDoesNotHaveCurrentPropertyTypeButHaveOldPropertyType(): void{
        $property = $this->createMock(core_kernel_classes_Property::class);
        $event = new PropertyChangedEvent(
            $property,
            new OldProperty('', $this->createMock(core_kernel_classes_Property::class))
        );
        $this->eventManager->expects($this->once())->method('trigger')->with($event);

        $this->sut->triggerIfNeeded(
            $event
        );
    }

    public function testTriggerIfHasChangesOnLabel(): void {

        $property = $this->createMock(core_kernel_classes_Property::class);

        $event = new PropertyChangedEvent(
            $property,
            new OldProperty('different', $property)
        );

        $this->eventManager->expects($this->once())->method('trigger')->with($event);

        $this->sut->triggerIfNeeded(
            $event
        );
    }

    public function testTriggerIfHasChangesOnPropertyType(): void {
        $property = $this->createPropertyMock('TextArea');

        $widgetProperty = $this->createMock(core_kernel_classes_Property::class);
        $widgetProperty->expects($this->once())
            ->method('getUri')
            ->willReturn('TextBox');

        $event = new PropertyChangedEvent(
            $property,
            new OldProperty('', $widgetProperty)
        );

        $this->eventManager->expects($this->once())->method('trigger')->with($event);

        $this->sut->triggerIfNeeded(
            $event
        );
    }

    private function createPropertyMock(string $widgetPropertyId = null): core_kernel_classes_Property
    {
        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->expects($this->once())
            ->method('getOnePropertyValue')
            ->with(new core_kernel_classes_Property(WidgetRdf::PROPERTY_WIDGET))
            ->willReturnCallback(
                function () use ($widgetPropertyId): core_kernel_classes_Property {
                    $widgetProperty = $this->createMock(core_kernel_classes_Property::class);
                    $widgetProperty->expects($this->any())
                        ->method('getUri')
                        ->willReturn($widgetPropertyId);

                    return $widgetProperty;
                }
            );
        return $property;
    }
}
