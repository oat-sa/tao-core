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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\DynamicConfig;

use oat\tao\model\DynamicConfig\DynamicConfigProviderInterface;
use oat\tao\model\DynamicConfig\EnvironmentVariableConfigProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EnvironmentVariableConfigProviderTest extends TestCase
{
    public function testGetConfigByName(): void
    {
        $configs = [
            DynamicConfigProviderInterface::LOGOUT_URL_CONFIG_NAME => 'https://example.com/logout',
            DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME => 'https://example.com/portal',
            DynamicConfigProviderInterface::LOGIN_URL_CONFIG_NAME => 'https://example.com/login',
        ];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with(
            'The Authoring As Tool config var with the name UNKNOWN_CONFIG is not available'
        );

        $provider = new EnvironmentVariableConfigProvider($configs, $logger);

        $this->assertSame(
            'https://example.com/logout',
            $provider->getConfigByName(EnvironmentVariableConfigProvider::LOGOUT_URL_CONFIG_NAME)
        );
        $this->assertSame(
            'https://example.com/portal',
            $provider->getConfigByName(EnvironmentVariableConfigProvider::PLATFORM_URL_CONFIG_NAME)
        );
        $this->assertSame(
            'https://example.com/login',
            $provider->getConfigByName(EnvironmentVariableConfigProvider::LOGIN_URL_CONFIG_NAME)
        );
        $this->assertNull($provider->getConfigByName('UNKNOWN_CONFIG'));
    }

    public function testGetConfigByNameAvailableButNotDefined(): void
    {
        $configs = [
            DynamicConfigProviderInterface::LOGOUT_URL_CONFIG_NAME => 'https://example.com/logout',
        ];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $provider = new EnvironmentVariableConfigProvider($configs, $logger);

        $this->assertNull($provider->getConfigByName(DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME));
    }

    public function testHasConfig(): void
    {
        $configs = [
            DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME => 'https://example.com/portal',
        ];

        $logger = $this->createMock(LoggerInterface::class);

        $provider = new EnvironmentVariableConfigProvider($configs, $logger);

        $this->assertTrue($provider->hasConfig(DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME));

        $provider = new EnvironmentVariableConfigProvider([], $logger);

        $this->assertFalse($provider->hasConfig(DynamicConfigProviderInterface::PLATFORM_URL_CONFIG_NAME));
    }
}
