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
use oat\tao\model\Lists\Business\Domain\ClassCollection;
use oat\tao\model\Lists\Business\Domain\ClassMetadata;
use Traversable;

class ClassCollectionTest extends TestCase
{
    public function testEmptyCollection()
    {
        $collection = new ClassCollection();

        $this->assertEquals(0, $collection->count());
        $this->assertEquals([], $collection->jsonSerialize());
        $this->assertTrue($collection->getIterator() instanceof Traversable);
    }

    public function testCreationWithItems()
    {
        $classMetadata1 = new ClassMetadata();
        $classMetadata2 = new ClassMetadata();
        $collection = new ClassCollection($classMetadata1, $classMetadata2);

        $this->assertEquals(2, $collection->count());
        $this->assertEquals([$classMetadata1, $classMetadata2], $collection->jsonSerialize());
    }

    public function testAddClassMetadata()
    {
        $collection = new ClassCollection();
        $class1 = new ClassMetadata();
        $class2 = new ClassMetadata();

        $this->assertEquals(0, $collection->count());
        $this->assertEquals([], $collection->jsonSerialize());

        $collection->addClassMetadata($class1);

        $this->assertEquals(1, $collection->count());
        $this->assertEquals([$class1], $collection->jsonSerialize());

        $collection->addClassMetadata($class2);

        $this->assertEquals(2, $collection->count());
        $this->assertEquals([$class1, $class2], $collection->jsonSerialize());
    }
}
