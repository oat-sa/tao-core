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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

use oat\tao\helpers\metadata\ResourceCompiledMetadataHelper;
use PHPUnit\Framework\TestCase;

class ResourceCompiledMetadataHelperTest extends TestCase
{
    /** @var ResourceCompiledMetadataHelper */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->object = new ResourceCompiledMetadataHelper();
    }

    /**
     * @param string $data
     * @param string $name
     * @param string|null $expectedValue
     *
     * @dataProvider providerTestGetValue
     */
    public function testGetValue($data, $name, $expectedValue)
    {
        $this->object->unserialize($data);
        $result = $this->object->getValue($name);

        $this->assertEquals($expectedValue, $result, 'Metadata parameter value must be as expected.');
    }

    /**
     * @param string $data
     * @param string|null $expectedLabel
     *
     * @dataProvider providerTestGetLabel
     */
    public function testGetLabel($data, $expectedLabel)
    {
        $this->object->unserialize($data);
        $label = $this->object->getLabel();

        $this->assertEquals($expectedLabel, $label, 'Label must be as expected.');
    }

    /**
     * Test unserialize when $data is not a string.
     *
     * @param mixed $data
     *
     * @dataProvider providerTestUnserializeNotString
     */
    public function testUnserializeNotString($data)
    {
        $this->expectException(common_exception_InconsistentData::class);

        $this->object->unserialize($data);
    }

    /**
     * Test unserialize method with invalid JSON string.
     */
    public function testUnserialiseInvalidJson()
    {
        $this->expectException(common_exception_InconsistentData::class);

        $data = "INVALID_JSON_STRING";
        $this->object->unserialize($data);
    }

    /**
     * @return array
     */
    public function providerTestUnserializeNotString()
    {
        return [
            'Array' => [
                'data' => [],
            ],
            'Object' => [
                'data' => new stdClass(),
            ],
            'Integer' => [
                'data' => 3,
            ],
            'Float' => [
                'data' => 5.3,
            ],
            'Boolean' => [
                'data' => true,
            ],
            'NULL' => [
                'data' => null,
            ],
        ];
    }

    /**
     * @return array
     */
    public function providerTestGetValue()
    {
        return [
            'Value exists' => [
                'data' => '{"type": "ResourceType"}',
                'name' => 'type',
                'expectedValue' => 'ResourceType',
            ],
            'Value does not exist' => [
                'data' => '{"type": "ResourceType"}',
                'name' => 'class',
                'expectedValue' => null,
            ],
        ];
    }

    /**
     * @return array
     */
    public function providerTestGetLabel()
    {
        return [
            'Label exists' => [
                'data' => '{"label": "Test Label"}',
                'expectedValue' => 'Test Label',
            ],
            'Label does not exist' => [
                'data' => '{"name": "Resource name"}',
                'expectedValue' => null,
            ],
        ];
    }
}
