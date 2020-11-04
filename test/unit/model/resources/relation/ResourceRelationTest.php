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

use oat\generis\test\TestCase;
use oat\tao\model\resources\relation\ResourceRelation;

class ResourceRelationTest extends TestCase
{
    public function testGetters(): void
    {
        $resourceRelation = (new ResourceRelation('item', '123', 'label'))
            ->withSourceId('456');

        $this->assertSame('item', $resourceRelation->getType());
        $this->assertSame('label', $resourceRelation->getLabel());
        $this->assertSame('123', $resourceRelation->getId());
        $this->assertSame('456', $resourceRelation->getSourceId());
        $this->assertSame(
            [
                'type' => 'item',
                'id' => '123',
                'label' => 'label',
            ],
            $resourceRelation->jsonSerialize()
        );
    }
}
