<?php

declare(strict_types=1);

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
use oat\generis\test\TestCase;
use oat\tao\model\plugins\PluginModule;

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
                    'id' => 'foo',
                    'name' => 'Foo',
                    'module' => 'plugin/foo',
                    'category' => 'dummy',
                    'description' => 'The best foo ever',
                    'active' => true,
                    'tags' => ['required'],
                ], [
                    'id' => 'foo',
                    'name' => 'Foo',
                    'module' => 'plugin/foo',
                    'category' => 'dummy',
                    'description' => 'The best foo ever',
                    'active' => true,
                    'tags' => ['required'],
                ],
            ], [
                [
                    'id' => '12',
                    'name' => 21,
                    'module' => 'plugin/foo',
                    'category' => 'dummy',
                ], [
                    'id' => '12',
                    'name' => '21',
                    'module' => 'plugin/foo',
                    'category' => 'dummy',
                    'description' => '',
                    'active' => true,
                    'tags' => [],
                ],
            ],
        ];
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadId(): void
    {
        new PluginModule(12, 'foo', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructEmptyId(): void
    {
        new PluginModule('', 'foo', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadModule(): void
    {
        new PluginModule('foo', true, 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructiEmptyModule(): void
    {
        new PluginModule('foo', '', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadCategory(): void
    {
        new PluginModule('foo', 'bar', []);
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructNoCategory(): void
    {
        new PluginModule('foo', 'bar', null);
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testFromArrayNoRequiredData(): void
    {
        PluginModule::fromArray([]);
    }

    /**
     * Test constructor and getter
     * @dataProvider accessorsProvider
     */
    public function testConstruct($input, $output): void
    {
        $PluginModule = new PluginModule($input['id'], $input['module'], $input['category'], $input);

        $this->assertSame($output['id'], $PluginModule->getId());
        $this->assertSame($output['name'], $PluginModule->getName());
        $this->assertSame($output['module'], $PluginModule->getModule());
        $this->assertSame($output['category'], $PluginModule->getCategory());
        $this->assertSame($output['description'], $PluginModule->getDescription());
        $this->assertSame($output['active'], $PluginModule->isActive());
        $this->assertSame($output['tags'], $PluginModule->getTags());

        $PluginModule->setActive(! $output['active']);
        $this->assertSame(! $output['active'], $PluginModule->isActive());
    }

    /**
     * Test from array and getters
     * @dataProvider accessorsProvider
     */
    public function testFromArray($input, $output): void
    {
        $PluginModule = PluginModule::fromArray($input);

        $this->assertSame($output['id'], $PluginModule->getId());
        $this->assertSame($output['name'], $PluginModule->getName());
        $this->assertSame($output['module'], $PluginModule->getModule());
        $this->assertSame($output['category'], $PluginModule->getCategory());
        $this->assertSame($output['description'], $PluginModule->getDescription());
        $this->assertSame($output['active'], $PluginModule->isActive());
        $this->assertSame($output['tags'], $PluginModule->getTags());

        $PluginModule->setActive(! $output['active']);
        $this->assertSame(! $output['active'], $PluginModule->isActive());
    }

    /**
     * Test encoding the object to json
     */
    public function testJsonSerialize(): void
    {
        $expected = '{"id":"bar","module":"bar\/bar","bundle":"plugins\/bundle.min","position":12,"name":"Bar","description":"The best bar ever","category":"dummy","active":false,"tags":["dummy","goofy"]}';

        $PluginModule = new PluginModule('bar', 'bar/bar', 'dummy', [
            'name' => 'Bar',
            'description' => 'The best bar ever',
            'active' => false,
            'position' => 12,
            'bundle' => 'plugins/bundle.min',
            'tags' => ['dummy', 'goofy'],
        ]);

        $serialized = json_encode($PluginModule);

        $this->assertSame($expected, $serialized);
    }
}
