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

namespace oat\tao\test\unit\AuthoringAsTool;

use oat\tao\model\AuthoringAsTool\AuthoringAsToolConfigProviderInterface;
use oat\tao\model\AuthoringAsTool\AuthoringAsToolEnvironmentVariableConfigProvider;
use PHPUnit\Framework\TestCase;

class AuthoringAsToolEnvironmentVariableConfigProviderTest extends TestCase
{
    public function testGetConfigByName(): void
    {
        $configs = [
            AuthoringAsToolConfigProviderInterface::LOGOUT_URL_CONFIG_NAME => 'https://example.com/logout',
            AuthoringAsToolConfigProviderInterface::PORTAL_URL_CONFIG_NAME => 'https://example.com/portal',
            AuthoringAsToolConfigProviderInterface::LOGIN_URL_CONFIG_NAME => 'https://example.com/login',
            'NULL_CONFIG' => null,
        ];

        $provider = new AuthoringAsToolEnvironmentVariableConfigProvider($configs);

        $this->assertSame('https://example.com/logout', $provider->getConfigByName(AuthoringAsToolEnvironmentVariableConfigProvider::LOGOUT_URL_CONFIG_NAME));
        $this->assertSame('https://example.com/portal', $provider->getConfigByName(AuthoringAsToolEnvironmentVariableConfigProvider::PORTAL_URL_CONFIG_NAME));
        $this->assertSame('https://example.com/login', $provider->getConfigByName(AuthoringAsToolEnvironmentVariableConfigProvider::LOGIN_URL_CONFIG_NAME));
        $this->assertNull($provider->getConfigByName('UNKNOWN_CONFIG'));
        $this->assertNull($provider->getConfigByName('NULL_CONFIG'));
    }

    public function testIsAuthoringAsToolEnabled(): void
    {
        $configs = [
            AuthoringAsToolConfigProviderInterface::PORTAL_URL_CONFIG_NAME => 'https://example.com/portal',
        ];

        $provider = new AuthoringAsToolEnvironmentVariableConfigProvider($configs);

        $this->assertTrue($provider->isAuthoringAsToolEnabled());

        $provider = new AuthoringAsToolEnvironmentVariableConfigProvider([]);

        $this->assertFalse($provider->isAuthoringAsToolEnabled());
    }
}
