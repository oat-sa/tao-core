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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\providers;

use common_exception_InconsistentData;
use oat\tao\model\providers\ProviderModule;
use PHPUnit\Framework\TestCase;

/**
 * Test the ProviderModule pojo
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class ProviderModuleTest extends TestCase
{
    /**
     * Data provider
     * @return array the data
     */
    public function accessorsProvider()
    {
        return [
            [
                [
                    'id' => 'foo',
                    'name' => 'Foo',
                    'module' => 'provider/foo',
                    'category' => 'dummy',
                    'description' => 'The best foo ever',
                    'active' => true,
                    'tags' => ['required']
                ], [
                'id' => 'foo',
                'name' => 'Foo',
                'module' => 'provider/foo',
                'category' => 'dummy',
                'description' => 'The best foo ever',
                'active' => true,
                'tags' => ['required']
                ]
            ], [
                [
                    'id' => '12',
                    'name' => 21,
                    'module' => 'provider/foo',
                    'category' => 'dummy',
                ], [
                    'id' => '12',
                    'name' => '21',
                    'module' => 'provider/foo',
                    'category' => 'dummy',
                    'description' => '',
                    'active' => true,
                    'tags' => []
                ]
            ]
        ];
    }

    public function testConstructBadId()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new ProviderModule(12, 'foo', 'bar');
    }


    public function testConstructEmptyId()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new ProviderModule('', 'foo', 'bar');
    }

    public function testConstructBadModule()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new ProviderModule('foo', true, 'bar');
    }

    public function testConstructiEmptyModule()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new ProviderModule('foo', '', 'bar');
    }

    public function testConstructBadCategory()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new ProviderModule('foo', 'bar', []);
    }

    public function testConstructNoCategory()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new ProviderModule('foo', 'bar', null);
    }

    public function testFromArrayNoRequiredData()
    {
        $this->expectException(common_exception_InconsistentData::class);
        ProviderModule::fromArray([]);
    }

    /**
     * Test constructor and getter
     * @dataProvider accessorsProvider
     */
    public function testConstruct($input, $output)
    {

        $ProviderModule = new ProviderModule($input['id'], $input['module'], $input['category'], $input);

        $this->assertEquals($output['id'], $ProviderModule->getId());
        $this->assertEquals($output['name'], $ProviderModule->getName());
        $this->assertEquals($output['module'], $ProviderModule->getModule());
        $this->assertEquals($output['category'], $ProviderModule->getCategory());
        $this->assertEquals($output['description'], $ProviderModule->getDescription());
        $this->assertEquals($output['active'], $ProviderModule->isActive());
        $this->assertEquals($output['tags'], $ProviderModule->getTags());

        $ProviderModule->setActive(!$output['active']);
        $this->assertEquals(!$output['active'], $ProviderModule->isActive());
    }

    /**
     * Test from array and getters
     * @dataProvider accessorsProvider
     */
    public function testFromArray($input, $output)
    {

        $ProviderModule = ProviderModule::fromArray($input);

        $this->assertEquals($output['id'], $ProviderModule->getId());
        $this->assertEquals($output['name'], $ProviderModule->getName());
        $this->assertEquals($output['module'], $ProviderModule->getModule());
        $this->assertEquals($output['category'], $ProviderModule->getCategory());
        $this->assertEquals($output['description'], $ProviderModule->getDescription());
        $this->assertEquals($output['active'], $ProviderModule->isActive());
        $this->assertEquals($output['tags'], $ProviderModule->getTags());

        $ProviderModule->setActive(!$output['active']);
        $this->assertEquals(!$output['active'], $ProviderModule->isActive());
    }

    /**
     * Test encoding the object to json
     */
    public function testJsonSerialize()
    {
        $expected = '{"id":"bar","module":"bar\/bar","bundle":"providers\/bundle.min","position":null,"name":"Bar",'
            . '"description":"The best bar ever","category":"dummy","active":false,"tags":["dummy","goofy"]}';

        $ProviderModule = new ProviderModule('bar', 'bar/bar', 'dummy', [
            'name' => 'Bar',
            'description' => 'The best bar ever',
            'active' => false,
            'bundle' => 'providers/bundle.min',
            'tags' => ['dummy', 'goofy']
        ]);

        $serialized = json_encode($ProviderModule);

        $this->assertEquals($expected, $serialized);
    }
}
