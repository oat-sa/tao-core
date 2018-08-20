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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\plugins\AbstractPluginRegistry;
use oat\tao\model\plugins\AbstractPluginService;
use oat\tao\model\plugins\PluginModule;
use Prophecy\Prophet;

/**
 * Concrete class PluginRegistry
 * @package oat\tao\test\unit\plugins
 */
class PluginRegistry extends AbstractPluginRegistry
{
    /**
     * @see \oat\oatbox\AbstractRegistry::getExtension()
     */
    protected function getExtension()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willExtend(\common_ext_Extension::class);

        return $prophecy->reveal();
    }

    /**
     * @see \oat\oatbox\AbstractRegistry::getConfigId()
     */
    protected function getConfigId()
    {
        return 'plugin_registry';
    }
}

/**
 * Concrete class PluginService
 * @package oat\tao\test\unit\plugins
 */
class PluginService extends AbstractPluginService
{

}

/**
 * Test the AbstractPluginService
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class AbstractPluginServiceTest extends \PHPUnit_Framework_TestCase
{
    //data to stub the registry content
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


    /**
     * Get the service with the stubbed registry
     * @return AbstractPluginService
     */
    protected function getPluginService()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willExtend(PluginRegistry::class);
        $prophecy->getMap()->willReturn(self::$pluginData);

        $pluginService = new PluginService();
        $pluginService->setRegistry($prophecy->reveal());

        return $pluginService;
    }

    /**
     * Check the service is a service
     */
    public function testApi()
    {
        $pluginService = $this->getPluginService();
        $this->assertInstanceOf(AbstractPluginService::class, $pluginService);
        $this->assertInstanceOf(ConfigurableService::class, $pluginService);
    }

    /**
     * Test the method AbstractPluginService::getAllPlugins
     */
    public function testGetAllPlugins()
    {
        $pluginService = $this->getPluginService();

        $plugins = $pluginService->getAllPlugins();

        $this->assertEquals(2, count($plugins));

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

    /**
     * Test the method AbstractPluginService::getPlugin
     */
    public function testGetOnePlugin()
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
