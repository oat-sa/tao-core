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

namespace oat\tao\test\unit\model\Lists\Business\Domain;

use PHPUnit\Framework\TestCase;
use oat\tao\model\Lists\Business\Domain\Metadata;
use oat\tao\model\Lists\Business\Domain\MetadataCollection;
use Traversable;

class MetadataCollectionTest extends TestCase
{
    public function testEmptyCollection()
    {
        $collection = new MetadataCollection();

        $this->assertEquals(0, $collection->count());
        $this->assertEquals([], $collection->jsonSerialize());
        $this->assertTrue($collection->getIterator() instanceof Traversable);
    }

    public function testCreationWithItems()
    {
        $metadata1 = new Metadata();
        $metadata2 = new Metadata();
        $collection = new MetadataCollection($metadata1, $metadata2);

        $this->assertEquals(2, $collection->count());
        $this->assertEquals([$metadata1, $metadata1], $collection->jsonSerialize());
    }

    public function testAddMetadata()
    {
        $collection = new MetadataCollection();
        $metadata1 = new Metadata();
        $metadata2 = new Metadata();

        $this->assertEquals(0, $collection->count());
        $this->assertEquals([], $collection->jsonSerialize());

        $collection->addMetadata($metadata1);

        $this->assertEquals(1, $collection->count());
        $this->assertEquals([$metadata1], $collection->jsonSerialize());

        $collection->addMetadata($metadata2);

        $this->assertEquals(2, $collection->count());
        $this->assertEquals([$metadata1, $metadata1], $collection->jsonSerialize());
    }
}
