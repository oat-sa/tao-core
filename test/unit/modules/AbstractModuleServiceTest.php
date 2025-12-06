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
 * Copyright (c) 2016-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\modules;

use common_ext_Extension;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\modules\AbstractModuleRegistry;
use oat\tao\model\modules\AbstractModuleService;
use oat\tao\model\modules\DynamicModule;
use PHPUnit\Framework\TestCase;

class ModuleRegistry extends AbstractModuleRegistry
{
    protected function getExtension(): common_ext_Extension
    {
        return new class extends common_ext_Extension {
        };
    }

    protected function getConfigId(): string
    {
        return 'module_registry';
    }
}

class ModuleService extends AbstractModuleService
{
}

class AbstractModuleServiceTest extends TestCase
{
    // data to stub the registry content
    private static $moduleData = [
        'my/wonderful/module/title' => [
            'id' => 'title',
            'module' => 'my/wonderful/module/title',
            'bundle' => 'modules/bundle.min',
            'position' => 1,
            'name' => 'Wonderful Title',
            'description' => 'Display a wonderful title',
            'category' => 'content',
            'active' => true,
            'tags' => ['core', 'component']
        ],
        'my/wonderful/module/text' => [
            'id' => 'text',
            'module' => 'my/wonderful/module/text',
            'bundle' => 'modules/bundle.min',
            'position' => 2,
            'name' => 'Wonderful Text',
            'description' => 'Display a wonderful text',
            'category' => 'content',
            'active' => true,
            'tags' => ['core', 'component']
        ]
    ];

    protected function getModuleService(): ModuleService
    {
        // Partial mock ModuleRegistry: подменяем только getMap()
        $registry = $this->getMockBuilder(ModuleRegistry::class)
            ->onlyMethods(['getMap'])
            ->getMock();

        $registry
            ->method('getMap')
            ->willReturn(self::$moduleData);

        $moduleService = new ModuleService();
        $moduleService->setRegistry($registry);

        return $moduleService;
    }

    public function testApi(): void
    {
        $moduleService = $this->getModuleService();
        $this->assertInstanceOf(AbstractModuleService::class, $moduleService);
        $this->assertInstanceOf(ConfigurableService::class, $moduleService);
    }

    public function testGetAllModules(): void
    {
        $moduleService = $this->getModuleService();

        $modules = $moduleService->getAllModules();

        $this->assertCount(2, $modules);

        $module0 = $modules['my/wonderful/module/title'];
        $module1 = $modules['my/wonderful/module/text'];

        $this->assertInstanceOf(DynamicModule::class, $module0);
        $this->assertInstanceOf(DynamicModule::class, $module1);

        $this->assertEquals('title', $module0->getId());
        $this->assertEquals('text', $module1->getId());

        $this->assertEquals('Wonderful Title', $module0->getName());
        $this->assertEquals('Wonderful Text', $module1->getName());

        $this->assertEquals(1, $module0->getPosition());
        $this->assertEquals(2, $module1->getPosition());

        $this->assertTrue($module0->isActive());
        $this->assertTrue($module1->isActive());
    }

    public function testGetOneModule(): void
    {
        $moduleService = $this->getModuleService();

        $module = $moduleService->getModule('text');

        $this->assertInstanceOf(DynamicModule::class, $module);
        $this->assertEquals('text', $module->getId());
        $this->assertEquals('Wonderful Text', $module->getName());
        $this->assertEquals(2, $module->getPosition());
        $this->assertEquals('my/wonderful/module/text', $module->getModule());
        $this->assertEquals('content', $module->getCategory());

        $this->assertTrue($module->isActive());
    }
}
