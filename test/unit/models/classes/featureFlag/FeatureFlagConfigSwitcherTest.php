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

    public function setUp(): void
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
        $this->markTestIncomplete();
    }

    public function testGetSwitchedExtensionConfig(): void
    {
        $this->markTestIncomplete();
    }

    public function testAddExtensionConfigHandler(): void
    {
        $this->markTestIncomplete();
    }

    public function testAddClientConfigHandler(): void
    {
        $this->markTestIncomplete();
    }

    public function testRemoveExtensionConfigHandler(): void
    {
        $this->markTestIncomplete();
    }

    public function testRemoveClientConfigHandler(): void
    {
        $this->markTestIncomplete();
    }
}
