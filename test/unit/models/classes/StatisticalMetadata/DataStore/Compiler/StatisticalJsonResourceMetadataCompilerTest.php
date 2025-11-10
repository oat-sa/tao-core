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

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\DataStore\Compiler;

use core_kernel_classes_Resource;
use PHPUnit\Framework\TestCase;
use oat\tao\model\metadata\compiler\ResourceMetadataCompilerInterface;
use oat\tao\model\StatisticalMetadata\DataStore\Compiler\StatisticalJsonResourceMetadataCompiler;
use PHPUnit\Framework\MockObject\MockObject;

class StatisticalJsonResourceMetadataCompilerTest extends TestCase
{
    /** @var ResourceMetadataCompilerInterface */
    private $sut;

    /** @var ResourceMetadataCompilerInterface|MockObject */
    private $resourceMetadataCompiler;

    protected function setUp(): void
    {
        $this->resourceMetadataCompiler = $this->createMock(ResourceMetadataCompilerInterface::class);

        $this->sut = new StatisticalJsonResourceMetadataCompiler($this->resourceMetadataCompiler);
    }

    public function testNotifyWithSuccess(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $resource->method('isInstanceOf')
            ->willReturn(true);

        $compiled = [
            '@context' => [
                'updated' => 'something that will be removed',
                'prop1' => 'prop1Id',
                'prop2' => 'prop2Id',
                'type' => 'typeId',
                'value' => 'valueId',
                'alias' => 'aliasId'
            ],
            '@id' => 'itemId',
            '@type' => 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            'updated' => 'something that will be removed',
            'prop1' => [
                'type' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
                'alias' => 'prop1alias',
                'label' => 'prop1',
                'value' => [
                    [
                        'label' => null,
                        'value' => 'item data 1'
                    ]
                ]
            ],
            'prop2' => [
                'type' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
                'alias' => 'prop2alias',
                'label' => 'prop2',
                'value' => [
                    [
                        'label' => null,
                        'value' => 'item data 2'
                    ]
                ]
            ]
        ];

        $this->resourceMetadataCompiler
            ->expects($this->once())
            ->method('compile')
            ->with($resource)
            ->willReturn($compiled);

        $this->assertSame(
            [
                '@context' => [
                    'prop1' => 'prop1Id',
                    'type' => 'typeId',
                    'value' => 'valueId',
                    'alias' => 'aliasId'
                ],
                '@id' => 'itemId',
                '@type' => 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
                'prop1' => [
                    'type' => 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox',
                    'alias' => 'prop1alias',
                    'label' => 'prop1',
                    'value' => [
                        [
                            'label' => null,
                            'value' => 'item data 1'
                        ]
                    ]
                ],
            ],
            $this->sut->withAliases(['prop1alias'])->compile($resource)
        );
    }
}
