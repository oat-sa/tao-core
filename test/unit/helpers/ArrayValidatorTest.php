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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\helpers;

use PHPUnit\Framework\TestCase;
use oat\tao\helpers\ArrayValidator;

class ArrayValidatorTest extends TestCase
{
    public function testRequiredFields()
    {
        $data = [
            'a' => [],
            's1' => '111',
            's2' => '222',
            'i' => 12,
            'b' => true,
            'f' => 234.5,
            'o' => new \DateTime(),
            'extra1' => 'ee'
        ];

        $validator = (new ArrayValidator())
            ->assertArray('a')
            ->assertString(['s1', 's2'])
            ->assertInt('i')
            ->assertBool('b')
            ->assertFloat('f')
            ->assertObject('o')
            ->allowExtraKeys();

        $this->assertTrue($validator->validate($data));
    }

    public function testRequiredFieldsNegative()
    {
        $data = [
            'a' => [],
            's1' => '111',
            'b' => true,
            'extra1' => 'ee'
        ];

        $validator = (new ArrayValidator())
            ->assertArray('a')
            ->assertString(['s1', 's2'])
            ->assertInt('i')
            ->assertExists('b')
            ->allowExtraKeys();

        $this->assertFalse($validator->validate($data));
        $this->assertNotNull($validator->getErrorMessage());
        $this->assertCount(2, $validator->getMissedKeys());
        $this->assertContains('s2', $validator->getMissedKeys());
        $this->assertContains('i', $validator->getMissedKeys());
        $this->assertEmpty($validator->getTypeMismatchKeys());
        $this->assertEmpty($validator->getExtraKeys());
    }

    public function testTypeMismatchedFields()
    {
        $data = [
            'a' => 'str',
            's1' => 123,
            's2' => new \DateTime(),
            'i' => null,
            'f' => 13,
            'b' => 'sss',
            'o' => 234,
            'extra1' => 'ee'
        ];

        $validator = (new ArrayValidator())
            ->assertArray('a')
            ->assertString(['s1', 's2'])
            ->assertInt('i')
            ->assertBool('b')
            ->assertFloat('f')
            ->assertObject('o')
            ->allowExtraKeys();

        $this->assertFalse($validator->validate($data));
        $this->assertNotNull($validator->getErrorMessage());
        $this->assertEmpty($validator->getMissedKeys());
        $this->assertCount(7, $validator->getTypeMismatchKeys());
        $this->assertArrayHasKey('a', $validator->getTypeMismatchKeys());
        $this->assertArrayHasKey('s1', $validator->getTypeMismatchKeys());
        $this->assertArrayHasKey('s2', $validator->getTypeMismatchKeys());
        $this->assertArrayHasKey('i', $validator->getTypeMismatchKeys());
        $this->assertArrayHasKey('b', $validator->getTypeMismatchKeys());
        $this->assertArrayHasKey('f', $validator->getTypeMismatchKeys());
        $this->assertArrayHasKey('o', $validator->getTypeMismatchKeys());
        $this->assertEmpty($validator->getExtraKeys());
    }

    public function testNullableFields()
    {
        $data = [
            'a' => null,
            's1' => null,
            's2' => 'sss',
            'i' => 123,
            'extra1' => 'ee'
        ];

        $validator = (new ArrayValidator())
            ->assertArray('a', true, true)
            ->assertString(['s1', 's2'], true, true)
            ->assertInt('i', true, true)
            ->allowExtraKeys();

        $this->assertTrue($validator->validate($data));
        $this->assertNull($validator->getErrorMessage());
        $this->assertEmpty($validator->getMissedKeys());
        $this->assertEmpty($validator->getTypeMismatchKeys());
        $this->assertEmpty($validator->getExtraKeys());
    }

    public function testOptionalFields()
    {
        $data = [
            'i' => 123,
            'extra1' => 'ee'
        ];

        $validator = (new ArrayValidator())
            ->assertArray('a', false)
            ->assertString(['s1', 's2'], false)
            ->assertInt('i')
            ->allowExtraKeys();

        $this->assertTrue($validator->validate($data));
        $this->assertNull($validator->getErrorMessage());
        $this->assertEmpty($validator->getMissedKeys());
        $this->assertEmpty($validator->getTypeMismatchKeys());
        $this->assertEmpty($validator->getExtraKeys());
    }

    public function testExtraFields()
    {
        $data = [
            'i' => 123,
            'extra1' => 'ee1',
            'extra2' => 'ee2'
        ];

        $validator = (new ArrayValidator())
            ->assertArray('a', false)
            ->assertString(['s1', 's2'], false)
            ->assertInt('i')
            ->allowExtraKeys(false);

        $this->assertFalse($validator->validate($data));
        $this->assertNotNull($validator->getErrorMessage());
        $this->assertEmpty($validator->getMissedKeys());
        $this->assertEmpty($validator->getTypeMismatchKeys());
        $this->assertCount(2, $validator->getExtraKeys());
        $this->assertContains('extra1', $validator->getExtraKeys());
        $this->assertContains('extra2', $validator->getExtraKeys());
    }
}
