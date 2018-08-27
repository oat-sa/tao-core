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

namespace oat\tao\test\unit\providers;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\modules\AbstractModuleRegistry;
use oat\tao\model\providers\AbstractProviderService;
use oat\tao\model\providers\ProviderModule;
use Prophecy\Prophet;

/**
 * Concrete class ProviderRegistry
 * @package oat\tao\test\unit\providers
 */
class ProviderRegistry extends AbstractModuleRegistry
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
        return 'provider_registry';
    }
}

/**
 * Concrete class ProviderService
 * @package oat\tao\test\unit\providers
 */
class ProviderService extends AbstractProviderService
{

}

/**
 * Test the AbstractProviderService
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class AbstractProviderServiceTest extends \PHPUnit_Framework_TestCase
{
    //data to stub the registry content
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


    /**
     * Get the service with the stubbed registry
     * @return AbstractProviderService
     */
    protected function getProviderService()
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willExtend(ProviderRegistry::class);
        $prophecy->getMap()->willReturn(self::$providerData);

        $providerService = new ProviderService();
        $providerService->setRegistry($prophecy->reveal());

        return $providerService;
    }

    /**
     * Check the service is a service
     */
    public function testApi()
    {
        $providerService = $this->getProviderService();
        $this->assertInstanceOf(AbstractProviderService::class, $providerService);
        $this->assertInstanceOf(ConfigurableService::class, $providerService);
    }

    /**
     * Test the method AbstractProviderService::getAllProviders
     */
    public function testGetAllProviders()
    {
        $providerService = $this->getProviderService();

        $providers = $providerService->getAllProviders();

        $this->assertEquals(2, count($providers));

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

    /**
     * Test the method AbstractProviderService::getProvider
     */
    public function testGetOneProvider()
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
