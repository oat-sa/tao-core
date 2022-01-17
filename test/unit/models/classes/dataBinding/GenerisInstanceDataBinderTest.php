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
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\action;

use oat\generis\test\MockObject;
use oat\generis\test\TestCase;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\event\MetadataModified;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use core_kernel_classes_Resource;


class GenerisInstanceDataBinderTest extends TestCase
{
    /** @var tao_models_classes_dataBinding_GenerisInstanceDataBinder */
    private $sut;

    /** @var core_kernel_classes_Resource|MockObject */
    private $resource;

    /** @var core_kernel_classes_Resource|EventManager */
    private $eventManagerMock;

    /** @var core_kernel_classes_Resource|ServiceManager */
    private $serviceManagerMock;

    public function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(EventManager::class);
        $this->serviceManagerMock = $this->createMock(ServiceManager::class);
        $services = [
            EventManager::SERVICE_ID => $this->eventManagerMock,
        ];

        $this->serviceManagerMock
            ->method('get')
            ->willReturnCallback(function ($id) use ($services) {
                return $services[$id];
            });

        $this->resource = $this->createMock(core_kernel_classes_Resource::class);
        $this->resource
            ->method('getTypes')
            ->willReturn(
                [
                    new \core_kernel_classes_Class('http://test.com/Type1'),
                    new \core_kernel_classes_Class('http://test.com/Type2'),
                ]
            );

        $this->sut = new tao_models_classes_dataBinding_GenerisInstanceDataBinder(
            $this->resource,
            $this->eventManagerMock
        );
    }

    public function testBind(): void
    {
        $this->eventManagerMock
            ->expects(self::once())
            ->method('trigger')
            ->with($this->callback(function (MetadataModified $event): bool {
                // NOTE: trigger() won't be called for the type property
                return (
                    $event->getResource()->getLabel() == $this->resource->getUri()
                    && ($event->getMetadataUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1')
                    && ($event->getMetadataValue() == 'Value 1')
                );
            }));

        $this->resource
            ->method('setType')
            ->withConsecutive(
                [
                    $this->callback(function (\core_kernel_classes_Class $class): bool {
                        return (
                            $class->getUri() == 'http://test.com/Type1'
                        );
                    })
                ]
            );

        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (\core_kernel_classes_Property $property) {
                    if ($property->getUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1') {
                        return new \core_kernel_classes_ContainerCollection(
                            new \common_Object()
                        );
                    }
                })
            );

        $data = [
            // type with a scalar value
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => 'http://test.com/Type1',
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1' => 'Value 1'
        ];

        $resource = $this->sut->bind($data);

        $this->assertClassesMatch(['http://test.com/Type1', 'http://test.com/Type2'], $resource);

    }

    public function testBind2(): void
    {
        $this->eventManagerMock
            ->expects(self::exactly(2))
            ->method('trigger')
            ->withConsecutive(
                [
                    $this->callback(function (MetadataModified $event): bool {
                        // NOTE: trigger() won't be called for the type property
                        return (
                            $event->getResource()->getLabel() == $this->resource->getUri()
                            && ($event->getMetadataUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1')
                            && ($event->getMetadataValue() == 'Value 1')
                        );
                    })
                ],
                [
                    $this->callback(function (MetadataModified $event): bool {
                        // NOTE: trigger() won't be called for the type property
                        return (
                            $event->getResource()->getLabel() == $this->resource->getUri()
                            && ($event->getMetadataUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop2')
                            && ($event->getMetadataValue() == 'Value 2')
                        );
                    })
                ]
            );

        $this->resource
            ->method('setType')
            ->withConsecutive([
                $this->callback(function (\core_kernel_classes_Class $class): bool {
                    return ($class->getUri() == 'http://test.com/Type1');
                }),
                $this->callback(function (\core_kernel_classes_Class $class): bool {
                    return ($class->getUri() == 'http://test.com/Type2');
                })
            ]);

        $this->resource
            ->method('getPropertyValuesCollection')
            ->will($this->returnCallback(
                function (\core_kernel_classes_Property $property) {
                    if ($property->getUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1') {
                        $collection = new \core_kernel_classes_ContainerCollection($this->resource);
                        $collection->add(new \core_kernel_classes_Literal('Value 1'));
                        return $collection;
                    }
                    if ($property->getUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop2') {
                        $collection = new \core_kernel_classes_ContainerCollection($this->resource);
                        $collection->add(new \core_kernel_classes_Literal('Value 2'));
                        return $collection;
                    }

                    $this->fail("getPropertyValuesCollection for unexpected property: {$property->getUri()}");
                })
            );

        $this->resource
            ->expects($this->exactly(2))
            ->method('editPropertyValues')
            ->willReturnCallback(function (\core_kernel_classes_Property $property, $value = null) {
                    if (($property->getUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1')
                        && ($value == 'Value 1')) {
                        return;
                    }
                    if (($property->getUri() == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop2')
                        && ($value == 'Value 2')) {
                        return;
                    }

                    $this->fail("editPropertyValues for unexpected property: {$property->getUri()}");
                }
            );

        $data = [
            // type with multiple values
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => [
                'http://test.com/Type1',
                'http://test.com/Type2',
            ],
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1' => 'Value 1',
            'http://www.w3.org/1999/02/22-rdf-syntax-ns#prop2' => 'Value 2',
        ];

        $resource = $this->sut->bind($data);

        $this->assertClassesMatch(['http://test.com/Type1', 'http://test.com/Type2'], $resource);
        /*$prop1 = $resource->getPropertyValues(
            new \core_kernel_classes_Property('http://www.w3.org/1999/02/22-rdf-syntax-ns#prop1')
        );

        $prop2 = $resource->getPropertyValues(
            new \core_kernel_classes_Property('http://www.w3.org/1999/02/22-rdf-syntax-ns#prop2')
        );*/

        //$this->assertCount(1, $prop1);
        //$this->assertCount(1, $prop2);
        /*$this->assertEquals('Value 1', $prop1[0]);
        $this->assertEquals('Value 2', $prop2[0]);*/
    }

    private function assertClassesMatch(array $expected, core_kernel_classes_Resource $resource)
    {
        $classes = array_map(function (\core_kernel_classes_Class $class) {
            return $class->getUri();
        }, $resource->getTypes());

        // Used to guarantee we always get the classes in the same order in tests.
        sort($classes);

        $this->assertEquals($expected, $classes);
    }
}
