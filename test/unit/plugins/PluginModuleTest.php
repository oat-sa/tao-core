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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\plugins;

use common_exception_InconsistentData;
use oat\tao\model\plugins\PluginModule;
use PHPUnit\Framework\TestCase;

/**
 * Test the PluginModule pojo
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class PluginModuleTest extends TestCase
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
                    'id'          => 'foo',
                    'name'        => 'Foo',
                    'module'      => 'plugin/foo',
                    'category'    => 'dummy',
                    'description' => 'The best foo ever',
                    'active'      => true,
                    'tags'        => ['required']
                ], [
                    'id'          => 'foo',
                    'name'        => 'Foo',
                    'module'      => 'plugin/foo',
                    'category'    => 'dummy',
                    'description' => 'The best foo ever',
                    'active'      => true,
                    'tags'        => ['required']
                ]
            ], [
                [
                    'id'          => '12',
                    'name'        => 21,
                    'module'      => 'plugin/foo',
                    'category'    => 'dummy',
                ], [
                    'id'          => '12',
                    'name'        => '21',
                    'module'      => 'plugin/foo',
                    'category'    => 'dummy',
                    'description' => '',
                    'active'      => true,
                    'tags'        => []
                ]
            ]
        ];
    }

    public function testConstructBadId()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new PluginModule(12, 'foo', 'bar');
    }


    public function testConstructEmptyId()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new PluginModule('', 'foo', 'bar');
    }

    public function testConstructBadModule()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new PluginModule('foo', true, 'bar');
    }

    public function testConstructiEmptyModule()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new PluginModule('foo', '', 'bar');
    }

    public function testConstructBadCategory()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new PluginModule('foo', 'bar', []);
    }

    public function testConstructNoCategory()
    {
        $this->expectException(common_exception_InconsistentData::class);
        new PluginModule('foo', 'bar', null);
    }

    public function testFromArrayNoRequiredData()
    {
        $this->expectException(common_exception_InconsistentData::class);
        PluginModule::fromArray([]);
    }

    /**
     * Test constructor and getter
     * @dataProvider accessorsProvider
     */
    public function testConstruct($input, $output)
    {

        $PluginModule = new PluginModule($input['id'], $input['module'], $input['category'], $input);

        $this->assertEquals($output['id'], $PluginModule->getId());
        $this->assertEquals($output['name'], $PluginModule->getName());
        $this->assertEquals($output['module'], $PluginModule->getModule());
        $this->assertEquals($output['category'], $PluginModule->getCategory());
        $this->assertEquals($output['description'], $PluginModule->getDescription());
        $this->assertEquals($output['active'], $PluginModule->isActive());
        $this->assertEquals($output['tags'], $PluginModule->getTags());

        $PluginModule->setActive(!$output['active']);
        $this->assertEquals(!$output['active'], $PluginModule->isActive());
    }

    /**
     * Test from array and getters
     * @dataProvider accessorsProvider
     */
    public function testFromArray($input, $output)
    {

        $PluginModule = PluginModule::fromArray($input);

        $this->assertEquals($output['id'], $PluginModule->getId());
        $this->assertEquals($output['name'], $PluginModule->getName());
        $this->assertEquals($output['module'], $PluginModule->getModule());
        $this->assertEquals($output['category'], $PluginModule->getCategory());
        $this->assertEquals($output['description'], $PluginModule->getDescription());
        $this->assertEquals($output['active'], $PluginModule->isActive());
        $this->assertEquals($output['tags'], $PluginModule->getTags());

        $PluginModule->setActive(!$output['active']);
        $this->assertEquals(!$output['active'], $PluginModule->isActive());
    }

    /**
     * Test encoding the object to json
     */
    public function testJsonSerialize()
    {
        $expected = '{"id":"bar","module":"bar\/bar","bundle":"plugins\/bundle.min","position":12,"name":"Bar",'
            . '"description":"The best bar ever","category":"dummy","active":false,"tags":["dummy","goofy"]}';

        $PluginModule = new PluginModule('bar', 'bar/bar', 'dummy', [
            'name' => 'Bar',
            'description' => 'The best bar ever',
            'active' =>  false,
            'position' => 12,
            'bundle' => 'plugins/bundle.min',
            'tags' => ['dummy', 'goofy']
        ]);

        $serialized = json_encode($PluginModule);

        $this->assertEquals($expected, $serialized);
    }
}
