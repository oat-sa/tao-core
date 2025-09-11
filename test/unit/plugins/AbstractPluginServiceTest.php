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

namespace oat\tao\test\unit\plugins;

use common_ext_Extension;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\modules\AbstractModuleRegistry;
use oat\tao\model\plugins\AbstractPluginService;
use oat\tao\model\plugins\PluginModule;
use PHPUnit\Framework\TestCase;

class PluginRegistry extends AbstractModuleRegistry
{
    protected function getExtension(): common_ext_Extension
    {
        return new class extends common_ext_Extension {
        };
    }

    protected function getConfigId(): string
    {
        return 'plugin_registry';
    }
}

class PluginService extends AbstractPluginService
{
}

class AbstractPluginServiceTest extends TestCase
{
    private static $pluginData = [
        'my/wonderful/plugin/title' => [
            'id' => 'title',
            'module' => 'my/wonderful/plugin/title',
            'bundle' => 'plugins/bundle.min',
            'position' => 1,
            'name' => 'Wonderful Title',
            'description' => 'Display a wonderful title',
            'category' => 'content',
            'active' => true,
            'tags' => ['core', 'component']
        ],
        'my/wonderful/plugin/text' => [
            'id' => 'text',
            'module' => 'my/wonderful/plugin/text',
            'bundle' => 'plugins/bundle.min',
            'position' => 2,
            'name' => 'Wonderful Text',
            'description' => 'Display a wonderful text',
            'category' => 'content',
            'active' => true,
            'tags' => ['core', 'component']
        ]
    ];

    protected function getPluginService(): AbstractPluginService
    {
        // partial mock: переопределяем только getMap()
        $registry = $this
            ->getMockBuilder(PluginRegistry::class)
            ->onlyMethods(['getMap'])
            ->getMock();

        $registry
            ->method('getMap')
            ->willReturn(self::$pluginData);

        $pluginService = new PluginService();
        $pluginService->setRegistry($registry);

        return $pluginService;
    }

    public function testApi(): void
    {
        $pluginService = $this->getPluginService();
        $this->assertInstanceOf(AbstractPluginService::class, $pluginService);
        $this->assertInstanceOf(ConfigurableService::class, $pluginService);
    }

    public function testGetAllPlugins(): void
    {
        $pluginService = $this->getPluginService();

        $plugins = $pluginService->getAllPlugins();

        $this->assertCount(2, $plugins);

        $plugin0 = $plugins['my/wonderful/plugin/title'];
        $plugin1 = $plugins['my/wonderful/plugin/text'];

        $this->assertInstanceOf(PluginModule::class, $plugin0);
        $this->assertInstanceOf(PluginModule::class, $plugin1);

        $this->assertEquals('title', $plugin0->getId());
        $this->assertEquals('text', $plugin1->getId());

        $this->assertEquals('Wonderful Title', $plugin0->getName());
        $this->assertEquals('Wonderful Text', $plugin1->getName());

        $this->assertEquals(1, $plugin0->getPosition());
        $this->assertEquals(2, $plugin1->getPosition());

        $this->assertTrue($plugin0->isActive());
        $this->assertTrue($plugin1->isActive());
    }

    public function testGetOnePlugin(): void
    {
        $pluginService = $this->getPluginService();

        $plugin = $pluginService->getPlugin('text');

        $this->assertInstanceOf(PluginModule::class, $plugin);
        $this->assertEquals('text', $plugin->getId());
        $this->assertEquals('Wonderful Text', $plugin->getName());
        $this->assertEquals(2, $plugin->getPosition());
        $this->assertEquals('my/wonderful/plugin/text', $plugin->getModule());
        $this->assertEquals('content', $plugin->getCategory());

        $this->assertTrue($plugin->isActive());
    }
}
