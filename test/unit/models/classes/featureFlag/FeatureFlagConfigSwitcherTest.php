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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\unit\test\model\featureFlag;

use common_ext_Extension;
use common_ext_ExtensionsManager;
use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FeatureFlagConfigSwitcherTest extends TestCase
{
    /** @var FeatureFlagConfigSwitcher */
    private $subject;

    /** @var ClientLibConfigRegistry|MockObject */
    private $registry;

    /** @var MockObject|ContainerInterface */
    private $container;

    /** @var common_ext_ExtensionsManager|MockObject */
    private $extensionManager;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ClientLibConfigRegistry::class);
        $this->extensionManager = $this->createMock(common_ext_ExtensionsManager::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->subject = new FeatureFlagConfigSwitcher(
            $this->registry,
            $this->extensionManager,
            $this->container
        );
    }

    public function testGetSwitchedClientConfig(): void
    {
        //@TODO Create test for non empty config
        $this->registry
            ->method('getMap')
            ->willReturn([]);

        $this->assertEquals(
            [],
            $this->subject->getSwitchedClientConfig()
        );
    }

    public function testGetSwitchedExtensionConfigEmpty(): void
    {
        //@TODO Create test for non empty config
        $extension = $this->createMock(common_ext_Extension::class);

        $this->registry
            ->method('getMap')
            ->willReturn([]);

        $this->extensionManager
            ->method('getExtensionById')
            ->willReturn($extension);

        $extension->method('getConfig')
            ->willReturn([]);

        $this->assertEquals(
            [],
            $this->subject->getSwitchedExtensionConfig('extensionName', 'configName')
        );
    }

    public function testAddExtensionConfigHandler(): void
    {
        //@TODO Improve test to assert added handler
        $this->assertNull(
            $this->subject->addExtensionConfigHandler(
                'extensionName',
                'configName',
                'handler'
            )
        );
    }

    public function testAddClientConfigHandler(): void
    {
        //@TODO Improve test to assert added handler
        $this->assertNull($this->subject->addClientConfigHandler('handler'));
    }
}
