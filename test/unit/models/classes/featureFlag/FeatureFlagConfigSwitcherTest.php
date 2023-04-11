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
 * Copyright (c) 2022-2023 (original work) Open Assessment Technologies SA.
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\unit\test\model\featureFlag;

use common_ext_Extension;
use oat\tao\model\clientConfig\ClientLibConfigSwitcher;
use PHPUnit\Framework\TestCase;
use common_ext_ExtensionsManager;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;

class FeatureFlagConfigSwitcherTest extends TestCase
{
    private FeatureFlagConfigSwitcher $subject;

    /** @var MockObject|ContainerInterface */
    private ContainerInterface $container;

    /** @var common_ext_ExtensionsManager|MockObject */
    private common_ext_ExtensionsManager $extensionManager;

    /** @var ClientLibConfigSwitcher|MockObject */
    private ClientLibConfigSwitcher $clientLibConfigSwitcher;

    public function setUp(): void
    {
        $this->extensionManager = $this->createMock(common_ext_ExtensionsManager::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->clientLibConfigSwitcher = $this->createMock(ClientLibConfigSwitcher::class);

        $this->subject = new FeatureFlagConfigSwitcher(
            $this->extensionManager,
            $this->container,
            $this->clientLibConfigSwitcher,
        );
    }

    public function testGetSwitchedClientConfig(): void
    {
        //@TODO Create test for non empty config
        $this->clientLibConfigSwitcher
            ->method('getSwitchedClientLibConfig')
            ->willReturn([]);

        $this->assertEquals([], $this->clientLibConfigSwitcher->getSwitchedClientLibConfig());
    }

    public function testGetSwitchedExtensionConfigEmpty(): void
    {
        //@TODO Create test for non empty config
        $extension = $this->createMock(common_ext_Extension::class);

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
}
