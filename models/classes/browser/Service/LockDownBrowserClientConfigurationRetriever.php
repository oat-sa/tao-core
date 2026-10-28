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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\browser\Service;

use InvalidArgumentException;
use oat\tao\model\browser\Entity\LockDownBrowserConfiguration;

class LockDownBrowserClientConfigurationRetriever
{
    public const LOCK_DOWN_BROWSER_CONFIG = 'LOCK_DOWN_BROWSER_CONFIG';

    public const DEFAULT_CONFIG = [
        'processCheckInterval' => 30000,
        'processCheckOnTestNavigation' => true,
        'processBlackList' => [],
        'mockActiveProcesses' => [],
        'simulateBreach' => false,
    ];

    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);

        if (empty($config) && !empty($_ENV[self::LOCK_DOWN_BROWSER_CONFIG])) {
            $decoded = json_decode((string)$_ENV[self::LOCK_DOWN_BROWSER_CONFIG], true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid lock down browser client JSON configuration: %s', json_last_error_msg())
                );
            }

            $this->config = array_merge($this->config, $decoded);
        }
    }

    public function retrieve(): LockDownBrowserConfiguration
    {
        $interval = (int)($this->config['processCheckInterval'] ?? null);
        $mockActiveProcesses = $this->config['mockActiveProcesses'] ?? [];

        if (!is_array($mockActiveProcesses)) {
            $mockActiveProcesses = [];
        }

        return new LockDownBrowserConfiguration(
            $interval > 0 ? $interval : null,
            (bool)($this->config['processCheckOnTestNavigation'] ?? true),
            is_array($this->config['processBlackList'] ?? null) ? $this->config['processBlackList'] : [],
            array_values(array_filter($mockActiveProcesses, 'is_string')),
            filter_var($this->config['simulateBreach'] ?? false, FILTER_VALIDATE_BOOLEAN)
        );
    }
}
