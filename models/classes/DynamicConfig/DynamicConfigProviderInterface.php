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

interface DynamicConfigProviderInterface
{
    public const LOGOUT_URL_CONFIG_NAME = 'logout';
    public const PLATFORM_URL_CONFIG_NAME = 'platform-url';
    public const LOGIN_URL_CONFIG_NAME = 'login';
    public const AVAILABLE_CONFIGS = [
        self::LOGOUT_URL_CONFIG_NAME,
        self::PLATFORM_URL_CONFIG_NAME,
        self::LOGIN_URL_CONFIG_NAME
    ];

    public function getConfigByName(string $name): ?string;

    public function hasConfig(string $name): bool;
}
