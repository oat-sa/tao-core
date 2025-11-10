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

declare(strict_types=1);

namespace oat\tao\test\unit\providers;

use common_ext_Extension;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\modules\AbstractModuleRegistry;
use oat\tao\model\providers\AbstractProviderService;
use oat\tao\model\providers\ProviderModule;
use PHPUnit\Framework\TestCase;

class ProviderRegistry extends AbstractModuleRegistry
{
    protected function getExtension(): common_ext_Extension
    {
        return new class extends common_ext_Extension {
        };
    }

    protected function getConfigId(): string
    {
        return 'provider_registry';
    }
}

class ProviderService extends AbstractProviderService
{
}

class AbstractProviderServiceTest extends TestCase
{
    // data to stub the registry content
    private static $providerData = [
        'my/wonderful/provider/foo1' => [
            'id' => 'foo1',
            'module' => 'my/wonderful/provider/foo1',
            'bundle' => 'providers/bundle.min',
            'name' => 'Wonderful Foo',
            'description' => 'Wonderful Foo Bar provider',
            'category' => 'content',
            'active' => true,
            'tags' => ['core', 'component']
        ],
        'my/wonderful/provider/foo2' => [
            'id' => 'foo2',
            'module' => 'my/wonderful/provider/foo2',
            'bundle' => 'providers/bundle.min',
            'name' => 'Wonderful Foo Bar',
            'description' => 'Display a wonderful foo bar',
            'category' => 'content',
            'active' => true,
            'tags' => ['core', 'component']
        ]
    ];

    protected function getProviderService(): AbstractProviderService
    {
        // Partial mock ProviderRegistry: подменяем только getMap()
        $registry = $this
            ->getMockBuilder(ProviderRegistry::class)
            ->onlyMethods(['getMap'])
            ->getMock();
        $registry
            ->method('getMap')
            ->willReturn(self::$providerData);

        $providerService = new ProviderService();
        $providerService->setRegistry($registry);

        return $providerService;
    }

    public function testApi(): void
    {
        $providerService = $this->getProviderService();
        $this->assertInstanceOf(AbstractProviderService::class, $providerService);
        $this->assertInstanceOf(ConfigurableService::class, $providerService);
    }

    public function testGetAllProviders(): void
    {
        $providerService = $this->getProviderService();

        $providers = $providerService->getAllProviders();

        $this->assertCount(2, $providers);

        $provider0 = $providers['my/wonderful/provider/foo1'];
        $provider1 = $providers['my/wonderful/provider/foo2'];

        $this->assertInstanceOf(ProviderModule::class, $provider0);
        $this->assertInstanceOf(ProviderModule::class, $provider1);

        $this->assertEquals('foo1', $provider0->getId());
        $this->assertEquals('foo2', $provider1->getId());

        $this->assertEquals('Wonderful Foo', $provider0->getName());
        $this->assertEquals('Wonderful Foo Bar', $provider1->getName());

        $this->assertTrue($provider0->isActive());
        $this->assertTrue($provider1->isActive());
    }

    public function testGetOneProvider(): void
    {
        $providerService = $this->getProviderService();

        $provider = $providerService->getProvider('foo1');

        $this->assertInstanceOf(ProviderModule::class, $provider);
        $this->assertEquals('foo1', $provider->getId());
        $this->assertEquals('Wonderful Foo', $provider->getName());
        $this->assertEquals('my/wonderful/provider/foo1', $provider->getModule());
        $this->assertEquals('content', $provider->getCategory());

        $this->assertTrue($provider->isActive());
    }
}
