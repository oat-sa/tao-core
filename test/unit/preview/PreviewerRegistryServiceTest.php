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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\preview;

use Prophecy\Prophet;
use Prophecy\Argument;
use oat\generis\test\TestCase;
use oat\tao\model\modules\DynamicModule;
use oat\tao\model\ClientLibConfigRegistry;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\preview\PreviewerRegistryService;
use common_exception_InconsistentData as InconsistentDataException;

/**
 * Class PreviewerRegistryServiceTest
 *
 * @package oat\tao\test\unit\preview
 */
class PreviewerRegistryServiceTest extends TestCase
{
    private const ADAPTER_DATA = [
        'stub/previewer/factory' => [
            'previewers' => [
                'stubTestPreviewer/previewer/adapter/test/qtiTest' => [
                    'id' => 'qtiTest',
                    'module' => 'stubTestPreviewer/previewer/adapter/test/qtiTest',
                    'bundle' => 'stubTestPreviewer/loader/qtiPreviewer.min',
                    'position' => null,
                    'name' => 'QTI Stub Previewer',
                    'description' => 'QTI implementation of the stub previewer',
                    'category' => 'previewer',
                    'active' => true,
                    'tags' => [
                        'core',
                        'qti',
                        'previewer',
                    ],
                ],
                'stubTestPreviewer/previewer/adapter/item/qtiItem' => [
                    'id' => 'qtiItem',
                    'module' => 'stubTestPreviewer/previewer/adapter/item/qtiItem',
                    'bundle' => 'stubTestPreviewer/loader/qtiPreviewer.min',
                    'position' => null,
                    'name' => 'QTI Stub Previewer',
                    'description' => 'QTI implementation of the stub previewer',
                    'category' => 'previewer',
                    'active' => false,
                    'tags' => [
                        'core',
                        'qti',
                        'previewer',
                    ],
                ],
            ],
            'plugins' => [
                [
                    'id' => 'plugin1',
                    'module' => 'stubTest/previewer/plugins/plugin1',
                    'bundle' => 'stubTest/loader/qtiPlugins.min',
                    'position' => null,
                    'name' => 'Plugin 1',
                    'description' => 'Sample plugin 1',
                    'category' => 'previewer',
                    'active' => true,
                    'tags' => [
                        'core',
                        'qti',
                        'previewer',
                    ],
                ],
                [
                    'id' => 'plugin2',
                    'module' => 'stubTest/previewer/plugins/plugin2',
                    'bundle' => 'stubTest/loader/qtiPlugins.min',
                    'position' => null,
                    'name' => 'Plugin 2',
                    'description' => 'Sample plugin 2',
                    'category' => 'previewer',
                    'active' => false,
                    'tags' => [
                        'core',
                        'qti',
                        'previewer',
                    ],
                ],
            ],
        ],
    ];

    /** @var PreviewerRegistryService */
    private $sut;

    /**
     * @before
     */
    public function init(): void
    {
        $this->sut = new PreviewerRegistryService('stub/previewer/factory');
        $this->sut->setRegistry($this->createAdapter());
    }

    public function testApi(): void
    {
        $this->assertInstanceOf(PreviewerRegistryService::class, $this->sut);
        $this->assertInstanceOf(ConfigurableService::class, $this->sut);
    }

    public function testGetAdapters(): void
    {
        $adapters = $this->sut->getAdapters();

        $this->assertCount(2, $adapters);

        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/test/qtiTest', $adapters);
        $secondAdapter = $adapters['stubTestPreviewer/previewer/adapter/test/qtiTest'];
        $this->assertArrayHasKey('id', $secondAdapter);
        $this->assertArrayHasKey('module', $secondAdapter);
        $this->assertArrayHasKey('bundle', $secondAdapter);
        $this->assertArrayHasKey('category', $secondAdapter);
        $this->assertArrayHasKey('active', $secondAdapter);

        $this->assertEquals('qtiTest', $secondAdapter['id']);
        $this->assertEquals('stubTestPreviewer/previewer/adapter/test/qtiTest', $secondAdapter['module']);
        $this->assertEquals('stubTestPreviewer/loader/qtiPreviewer.min', $secondAdapter['bundle']);
        $this->assertEquals('previewer', $secondAdapter['category']);
        $this->assertEquals(true, $secondAdapter['active']);

        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/item/qtiItem', $adapters);
        $firstAdapter = $adapters['stubTestPreviewer/previewer/adapter/item/qtiItem'];
        $this->assertArrayHasKey('id', $firstAdapter);
        $this->assertArrayHasKey('module', $firstAdapter);
        $this->assertArrayHasKey('bundle', $firstAdapter);
        $this->assertArrayHasKey('category', $firstAdapter);
        $this->assertArrayHasKey('active', $firstAdapter);

        $this->assertEquals('qtiItem', $firstAdapter['id']);
        $this->assertEquals('stubTestPreviewer/previewer/adapter/item/qtiItem', $firstAdapter['module']);
        $this->assertEquals('stubTestPreviewer/loader/qtiPreviewer.min', $firstAdapter['bundle']);
        $this->assertEquals('previewer', $firstAdapter['category']);
        $this->assertEquals(false, $firstAdapter['active']);
    }

    /**
     * @throws InconsistentDataException
     */
    public function testRegisterAdapter(): void
    {
        $adapters = $this->sut->getAdapters();

        $this->assertCount(2, $adapters);

        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/test/qtiTest', $adapters);
        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/item/qtiItem', $adapters);
        $this->assertArrayNotHasKey('stubTest/previewer/adapter/qtiMock', $adapters);

        $module = DynamicModule::fromArray([
            'id' => 'qtiMock',
            'name' => 'QTI Mock Previewer',
            'module' => 'stubTest/previewer/adapter/qtiMock',
            'bundle' => 'stubTestPreviewer/loader/qtiPreviewer.min',
            'description' => 'QTI implementation of the stub previewer',
            'category' => 'previewer',
            'active' => true,
            'tags' => ['core', 'qti', 'previewer'],
        ]);
        $this->assertEquals(true, $this->sut->registerAdapter($module));

        $adapters = $this->sut->getAdapters();
        $this->assertCount(3, $adapters);
        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/test/qtiTest', $adapters);
        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/item/qtiItem', $adapters);
        $this->assertArrayHasKey('stubTest/previewer/adapter/qtiMock', $adapters);
    }

    public function testUnregisterAdapter(): void
    {
        $adapters = $this->sut->getAdapters();

        $this->assertCount(2, $adapters);

        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/test/qtiTest', $adapters);
        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/item/qtiItem', $adapters);

        $this->assertEquals(
            true,
            $this->sut->unregisterAdapter('stubTestPreviewer/previewer/adapter/test/qtiTest')
        );

        $adapters = $this->sut->getAdapters();
        $this->assertCount(1, $adapters);
        $this->assertArrayNotHasKey('stubTestPreviewer/previewer/adapter/test/qtiTest', $adapters);
        $this->assertArrayHasKey('stubTestPreviewer/previewer/adapter/item/qtiItem', $adapters);

        $this->assertEquals(
            false,
            $this->sut->unregisterAdapter('stubTestPreviewer/previewer/adapter/test/qtiTest')
        );
    }

    public function testRegisterPlugin(): void
    {
        $plugins = $this->sut->getPlugins();

        $this->assertCount(2, $plugins);

        $this->assertArrayHasKey('0', $plugins);
        $this->assertArrayHasKey('id', $plugins[0]);
        $this->assertEquals('plugin1', $plugins[0]['id']);

        $this->assertArrayHasKey('1', $plugins);
        $this->assertArrayHasKey('id', $plugins[1]);
        $this->assertEquals('plugin2', $plugins[1]['id']);

        $this->assertArrayNotHasKey('2', $plugins);

        $module = DynamicModule::fromArray(
            [
                'id' => 'plugin3',
                'module' => 'stubTest/previewer/plugins/plugin3',
                'bundle' => 'stubTest/loader/qtiPlugins.min',
                'name' => 'Plugin 3',
                'description' => 'Sample plugin 3',
                'category' => 'previewer',
                'active' => true,
                'tags' => []
            ]
        );
        $this->assertEquals(true, $this->sut->registerPlugin($module));

        $plugins = $this->sut->getPlugins();
        $this->assertCount(3, $plugins);

        $this->assertArrayHasKey('0', $plugins);
        $this->assertArrayHasKey('id', $plugins[0]);
        $this->assertEquals('plugin1', $plugins[0]['id']);

        $this->assertArrayHasKey('1', $plugins);
        $this->assertArrayHasKey('id', $plugins[1]);
        $this->assertEquals('plugin2', $plugins[1]['id']);

        $this->assertArrayHasKey('2', $plugins);
        $this->assertArrayHasKey('id', $plugins[2]);
        $this->assertEquals('plugin3', $plugins[2]['id']);

        $module = DynamicModule::fromArray([
            'id' => 'plugin3bis',
            'module' => 'stubTest/previewer/plugins/plugin3',
            'bundle' => 'stubTest/loader/qtiPlugins.min',
            'name' => 'Plugin 3 bis',
            'description' => 'Sample plugin 3',
            'category' => 'previewer',
            'active' => true,
            'tags' => []
        ]);
        $this->assertEquals(true, $this->sut->registerPlugin($module));

        $plugins = $this->sut->getPlugins();
        $this->assertCount(3, $plugins);
        $this->assertArrayHasKey('2', $plugins);
        $this->assertArrayHasKey('id', $plugins[2]);
        $this->assertEquals('plugin3bis', $plugins[2]['id']);
    }

    public function testUnregisterPlugin(): void
    {
        $plugins = $this->sut->getPlugins();

        $this->assertCount(2, $plugins);

        $this->assertArrayHasKey('0', $plugins);
        $this->assertArrayHasKey('id', $plugins[0]);
        $this->assertEquals('plugin1', $plugins[0]['id']);

        $this->assertArrayHasKey('1', $plugins);
        $this->assertArrayHasKey('id', $plugins[1]);
        $this->assertEquals('plugin2', $plugins[1]['id']);

        $this->assertEquals(true, $this->sut->unregisterPlugin('stubTest/previewer/plugins/plugin2'));

        $plugins = $this->sut->getPlugins();
        $this->assertCount(1, $plugins);
        $this->assertArrayHasKey('0', $plugins);
        $this->assertArrayHasKey('id', $plugins[0]);
        $this->assertEquals('plugin1', $plugins[0]['id']);

        $this->assertArrayNotHasKey('1', $plugins);

        $this->assertEquals(false, $this->sut->unregisterPlugin('stubTest/previewer/plugins/plugin2'));
    }

    /**
     * @return ClientLibConfigRegistry
     */
    private function createAdapter(): ClientLibConfigRegistry
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize(ClientLibConfigRegistry::class);
        $data = self::ADAPTER_DATA;

        $prophecy
            ->isRegistered(Argument::type('string'))
            ->will(function ($args) use (&$data) {
                return isset($data[$args[0]]);
            });
        $prophecy
            ->get(Argument::type('string'))
            ->will(function ($args) use (&$data) {
                return $data[$args[0]];
            });
        $prophecy
            ->set(Argument::type('string'), Argument::type('array'))
            ->will(function ($args) use (&$data) {
                $data[$args[0]] = $args[1];
            });

        return $prophecy->reveal();
    }
}
