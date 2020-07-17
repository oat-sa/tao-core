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

namespace oat\tao\model\Lists\Business\Domain;

use oat\generis\test\TestCase;

class CollectionTypeTest extends TestCase
{
    /**
     * @covers \oat\tao\model\Lists\Business\Domain\CollectionType::remote
     * @covers \oat\tao\model\Lists\Business\Domain\CollectionType::remote
     * @covers \oat\tao\model\Lists\Business\Domain\CollectionType::fromValue
     * @covers \oat\tao\model\Lists\Business\Domain\CollectionType::equals
     */
    public function testFromValue(): void
    {
        $enum1 = CollectionType::fromValue('');
        $enum2 = CollectionType::fromValue('http://www.tao.lu/Ontologies/TAO.rdf#ListRemote');

        $this->assertTrue($enum1->equals(CollectionType::default()));
        $this->assertTrue($enum2->equals(CollectionType::remote()));
    }

    /**
     * @covers \oat\tao\model\Lists\Business\Domain\CollectionType::__toString
     */
    public function testToString(): void
    {
        $enum1 = CollectionType::default();
        $enum2 = CollectionType::remote();

        $this->assertEquals('', (string)$enum1);
        $this->assertEquals('http://www.tao.lu/Ontologies/TAO.rdf#ListRemote', (string)$enum2);
    }
}
