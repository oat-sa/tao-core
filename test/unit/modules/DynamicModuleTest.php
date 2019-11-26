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

namespace oat\tao\test\unit\modules;

use common_exception_InconsistentData;
use oat\generis\test\TestCase;
use oat\tao\model\modules\DynamicModule;

/**
 * Test the DynamicModule pojo
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class DynamicModuleTest extends TestCase
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
                    'module' => 'module/foo',
                    'category' => 'dummy',
                    'description' => 'The best foo ever',
                    'active' => true,
                    'tags' => ['required'],
                ], [
                    'id' => 'foo',
                    'name' => 'Foo',
                    'module' => 'module/foo',
                    'category' => 'dummy',
                    'description' => 'The best foo ever',
                    'active' => true,
                    'tags' => ['required'],
                ],
            ], [
                [
                    'id' => '12',
                    'name' => 21,
                    'module' => 'module/foo',
                    'category' => 'dummy',
                ], [
                    'id' => '12',
                    'name' => '21',
                    'module' => 'module/foo',
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
        new DynamicModule(12, 'foo', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructEmptyId(): void
    {
        new DynamicModule('', 'foo', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadModule(): void
    {
        new DynamicModule('foo', true, 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructiEmptyModule(): void
    {
        new DynamicModule('foo', '', 'bar');
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructBadCategory(): void
    {
        new DynamicModule('foo', 'bar', []);
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testConstructNoCategory(): void
    {
        new DynamicModule('foo', 'bar', null);
    }

    /**
     * @expectedException common_exception_InconsistentData
     */
    public function testFromArrayNoRequiredData(): void
    {
        DynamicModule::fromArray([]);
    }

    /**
     * Test constructor and getter
     * @dataProvider accessorsProvider
     */
    public function testConstruct($input, $output): void
    {
        $DynamicModule = new DynamicModule($input['id'], $input['module'], $input['category'], $input);

        $this->assertSame($output['id'], $DynamicModule->getId());
        $this->assertSame($output['name'], $DynamicModule->getName());
        $this->assertSame($output['module'], $DynamicModule->getModule());
        $this->assertSame($output['category'], $DynamicModule->getCategory());
        $this->assertSame($output['description'], $DynamicModule->getDescription());
        $this->assertSame($output['active'], $DynamicModule->isActive());
        $this->assertSame($output['tags'], $DynamicModule->getTags());

        $DynamicModule->setActive(! $output['active']);
        $this->assertSame(! $output['active'], $DynamicModule->isActive());
    }

    /**
     * Test from array and getters
     * @dataProvider accessorsProvider
     */
    public function testFromArray($input, $output): void
    {
        $DynamicModule = DynamicModule::fromArray($input);

        $this->assertSame($output['id'], $DynamicModule->getId());
        $this->assertSame($output['name'], $DynamicModule->getName());
        $this->assertSame($output['module'], $DynamicModule->getModule());
        $this->assertSame($output['category'], $DynamicModule->getCategory());
        $this->assertSame($output['description'], $DynamicModule->getDescription());
        $this->assertSame($output['active'], $DynamicModule->isActive());
        $this->assertSame($output['tags'], $DynamicModule->getTags());

        $DynamicModule->setActive(! $output['active']);
        $this->assertSame(! $output['active'], $DynamicModule->isActive());
    }

    /**
     * Test encoding the object to json
     */
    public function testJsonSerialize(): void
    {
        $expected = '{"id":"bar","module":"bar\/bar","bundle":"modules\/bundle.min","position":12,"name":"Bar","description":"The best bar ever","category":"dummy","active":false,"tags":["dummy","goofy"]}';

        $DynamicModule = new DynamicModule('bar', 'bar/bar', 'dummy', [
            'name' => 'Bar',
            'description' => 'The best bar ever',
            'active' => false,
            'position' => 12,
            'bundle' => 'modules/bundle.min',
            'tags' => ['dummy', 'goofy'],
        ]);

        $serialized = json_encode($DynamicModule);

        $this->assertSame($expected, $serialized);
    }
}
