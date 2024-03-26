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

namespace oat\tao\model\DynamicConfig;

use Psr\Log\LoggerInterface;

class EnvironmentVariableConfigProvider implements DynamicConfigProviderInterface
{
    public const ENV_REDIRECT_AFTER_LOGOUT_URL = 'REDIRECT_AFTER_LOGOUT_URL';
    public const ENV_PORTAL_URL = 'PORTAL_URL';
    public const ENV_TAO_LOGIN_URL = 'TAO_LOGIN_URL';

    private array $configs;
    private LoggerInterface $logger;

    public function __construct(array $configs, LoggerInterface $logger)
    {
        $this->configs = $configs;
        $this->logger = $logger;
    }

    public function getConfigByName(string $name): ?string
    {
        if (!in_array($name, self::AVAILABLE_CONFIGS)) {
            $this->logger->warning(
                sprintf('The Authoring As Tool config var with the name %s is not available', $name)
            );
            return null;
        }

        return $this->configs[$name] ?? null;
    }

    public function hasConfig(string $name): bool
    {
        return $this->getConfigByName($name) !== null;
    }
}
