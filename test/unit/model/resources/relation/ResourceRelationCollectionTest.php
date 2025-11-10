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

namespace oat\test\unit\model\resources\relation;

use PHPUnit\Framework\TestCase;
use oat\tao\model\resources\relation\ResourceRelation;
use oat\tao\model\resources\relation\ResourceRelationCollection;

class ResourceRelationCollectionTest extends TestCase
{
    /** @var ResourceRelationCollection */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResourceRelationCollection(
            ...[
                (new ResourceRelation('item', '123', 'label item'))->withSourceId('456'),
            ]
        );
        $this->subject->add((new ResourceRelation('media', '321', 'label media'))->withSourceId('654'));
    }

    public function testGetIterator(): void
    {
        $this->assertSame(2, $this->subject->getIterator()->count());
        $this->assertInstanceOf(ResourceRelation::class, $this->subject->getIterator()->offsetGet(0));
        $this->assertInstanceOf(ResourceRelation::class, $this->subject->getIterator()->offsetGet(1));
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(
            [
                [
                    'type' => 'item',
                    'id' => '123',
                    'label' => 'label item',

                ],
                [
                    'type' => 'media',
                    'id' => '321',
                    'label' => 'label media',

                ]
            ],
            json_decode(json_encode($this->subject->jsonSerialize()), true)
        );
    }

    public function testFilterRemovedSourceIds(): void
    {
        $this->assertSame(
            [
                '654'
            ],
            array_values(
                $this->subject->filterRemovedSourceIds(
                    [
                        '456'
                    ]
                )
            )
        );

        $this->assertSame(
            [],
            $this->subject->filterRemovedSourceIds(
                [
                    '456',
                    '654'
                ]
            )
        );
    }

    public function testFilterNewSourceIds(): void
    {
        $this->assertSame(
            [
                '777'
            ],
            array_values(
                $this->subject->filterNewSourceIds(
                    [
                        '777',
                        '456',
                        '654',
                    ]
                )
            )
        );

        $this->assertSame(
            [],
            $this->subject->filterNewSourceIds(
                [
                    '456',
                    '654'
                ]
            )
        );
    }
}
