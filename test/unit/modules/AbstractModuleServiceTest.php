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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\modules\AbstractModuleRegistry;
use oat\tao\model\modules\AbstractModuleService;
use oat\tao\model\modules\DynamicModule;
use Prophecy\Prophet;

/**
 * Concrete class ModuleRegistry
 * @package oat\tao\test\unit\modules
 */
class ModuleRegistry extends AbstractModuleRegistry
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
        return 'module_registry';
    }
}

/**
 * Concrete class ModuleService
 * @package oat\tao\test\unit\modules
 */
class ModuleService extends AbstractModuleService
{

}

/**
 * Test the AbstractModuleService
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class AbstractModuleServiceTest extends \PHPUnit_Framework_TestCase
{
    //data to stub the registry content
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


    /**
     * Get the service with the stubbed registry
     * @return AbstractModuleService
     */
    protected function getModuleService()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willExtend(ModuleRegistry::class);
        $prophecy->getMap()->willReturn(self::$moduleData);

        $moduleService = new ModuleService();
        $moduleService->setRegistry($prophecy->reveal());

        return $moduleService;
    }

    /**
     * Check the service is a service
     */
    public function testApi()
    {
        $moduleService = $this->getModuleService();
        $this->assertInstanceOf(AbstractModuleService::class, $moduleService);
        $this->assertInstanceOf(ConfigurableService::class, $moduleService);
    }

    /**
     * Test the method AbstractModuleService::getAllModules
     */
    public function testGetAllModules()
    {
        $moduleService = $this->getModuleService();

        $modules = $moduleService->getAllModules();

        $this->assertEquals(2, count($modules));

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

    /**
     * Test the method AbstractModuleService::getModule
     */
    public function testGetOneModule()
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
