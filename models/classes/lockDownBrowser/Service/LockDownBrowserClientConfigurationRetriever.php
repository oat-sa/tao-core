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

namespace oat\tao\model\lockDownBrowser\Service;

use InvalidArgumentException;
use oat\tao\model\lockDownBrowser\Entity\LockDownBrowserClientConfiguration;

final class LockDownBrowserClientConfigurationRetriever
{
    public const LOCK_DOWN_BROWSER_CLIENT_CONFIG = 'LOCK_DOWN_BROWSER_CLIENT_CONFIG';

    public const DEFAULT_CONFIG = [
        'processCheckInterval' => 5000, // @TODO restore 60000 for production
        'processCheckOnTestNavigation' => true,
        // @TODO replace PoC deny-list before go-live; mockActiveProcesses must name processes from this list for mock breach QA
        'processBlackList' => [
            ['process' => 'notion', 'label' => 'Notion'],
        ],
        'mockActiveProcesses' => ['notion'],
        'simulateBreach' => true,
    ];

    private ?string $jsonConfig;

    public function __construct(?string $jsonConfig = null)
    {
        $this->jsonConfig = $jsonConfig;
    }

    public function retrieve(): LockDownBrowserClientConfiguration
    {
        $config = self::DEFAULT_CONFIG;

        if (!empty($this->jsonConfig)) {
            $decoded = json_decode($this->jsonConfig, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException(
                    sprintf('Invalid lock down browser client JSON configuration: %s', json_last_error_msg())
                );
            }

            if (is_array($decoded)) {
                $config = array_merge($config, $decoded);
            }
        }

        $interval = $config['processCheckInterval'] ?? null;

        if ($interval !== null) {
            $interval = (int)$interval;

            if ($interval <= 0) {
                $interval = null;
            }
        }

        $mockActiveProcesses = $config['mockActiveProcesses'] ?? [];

        if (!is_array($mockActiveProcesses)) {
            $mockActiveProcesses = [];
        }

        return new LockDownBrowserClientConfiguration(
            $interval,
            (bool)($config['processCheckOnTestNavigation'] ?? true),
            is_array($config['processBlackList'] ?? null) ? $config['processBlackList'] : [],
            array_values(array_filter($mockActiveProcesses, 'is_string')),
            filter_var($config['simulateBreach'] ?? false, FILTER_VALIDATE_BOOLEAN)
        );
    }
}
