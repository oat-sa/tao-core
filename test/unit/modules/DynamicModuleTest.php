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
namespace oat\tao\test\unit\modules;

use common_exception_InconsistentData;
use oat\tao\model\modules\DynamicModule;

/**
 * Test the DynamicModule pojo
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class DynamicModuleTestModuleTest extends \PHPUnit_Framework_TestCase
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
                    'module'      => 'module/foo',
                    'category'    => 'dummy',
                    'description' => 'The best foo ever',
                    'active'      => true,
                    'tags'        => ['required']
                ], [
                    'id'          => 'foo',
                    'name'        => 'Foo',
                    'module'      => 'module/foo',
                    'category'    => 'dummy',
                    'description' => 'The best foo ever',
                    'active'      => true,
                    'tags'        => ['required']
                ]
            ], [
                [
                    'id'          => '12',
                    'name'        => 21,
                    'module'      => 'module/foo',
                    'category'    => 'dummy',
                ], [
                    'id'          => '12',
                    'name'        => '21',
                    'module'      => 'module/foo',
                    'category'    => 'dummy',
                    'description' => '',
                    'active'      => true,
                    'tags'        => []
                ]
            ]
        ];
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadId()
    {
        new DynamicModule(12, 'foo', 'bar');
    }


    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructEmptyId()
    {
        new DynamicModule('', 'foo', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadModule()
    {
        new DynamicModule('foo', true, 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructiEmptyModule()
    {
        new DynamicModule('foo', '', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadCategory()
    {
        new DynamicModule('foo', 'bar', []);
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructNoCategory()
    {
        new DynamicModule('foo', 'bar', null);
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testFromArrayNoRequiredData()
    {
        DynamicModule::fromArray([]);
    }

    /**
     * Test constructor and getter
     * @dataProvider accessorsProvider
     */
    public function testConstruct($input, $output)
    {

        $DynamicModule = new DynamicModule($input['id'], $input['module'], $input['category'], $input);

        $this->assertEquals($output['id'], $DynamicModule->getId());
        $this->assertEquals($output['name'], $DynamicModule->getName());
        $this->assertEquals($output['module'], $DynamicModule->getModule());
        $this->assertEquals($output['category'], $DynamicModule->getCategory());
        $this->assertEquals($output['description'], $DynamicModule->getDescription());
        $this->assertEquals($output['active'], $DynamicModule->isActive());
        $this->assertEquals($output['tags'], $DynamicModule->getTags());

        $DynamicModule->setActive(!$output['active']);
        $this->assertEquals(!$output['active'], $DynamicModule->isActive());
    }

    /**
     * Test from array and getters
     * @dataProvider accessorsProvider
     */
    public function testFromArray($input, $output)
    {

        $DynamicModule = DynamicModule::fromArray($input);

        $this->assertEquals($output['id'], $DynamicModule->getId());
        $this->assertEquals($output['name'], $DynamicModule->getName());
        $this->assertEquals($output['module'], $DynamicModule->getModule());
        $this->assertEquals($output['category'], $DynamicModule->getCategory());
        $this->assertEquals($output['description'], $DynamicModule->getDescription());
        $this->assertEquals($output['active'], $DynamicModule->isActive());
        $this->assertEquals($output['tags'], $DynamicModule->getTags());

        $DynamicModule->setActive(!$output['active']);
        $this->assertEquals(!$output['active'], $DynamicModule->isActive());
    }

    /**
     * Test encoding the object to json
     */
    public function testJsonSerialize()
    {
        $expected = '{"id":"bar","module":"bar\/bar","bundle":"modules\/bundle.min","position":12,"name":"Bar","description":"The best bar ever","category":"dummy","active":false,"tags":["dummy","goofy"]}';

        $DynamicModule = new DynamicModule('bar', 'bar/bar', 'dummy', [
            'name' => 'Bar',
            'description' => 'The best bar ever',
            'active' =>  false,
            'position' => 12,
            'bundle' => 'modules/bundle.min',
            'tags' => ['dummy', 'goofy']
        ]);

        $serialized = json_encode($DynamicModule);

        $this->assertEquals($expected, $serialized);
    }
}
