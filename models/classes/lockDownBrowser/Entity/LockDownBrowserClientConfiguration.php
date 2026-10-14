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

namespace oat\tao\model\lockDownBrowser\Entity;

use JsonSerializable;

/**
 * Process-check and mock settings for lock down browser in client config (driver/active: test runner plugin registry).
 */
final class LockDownBrowserClientConfiguration implements JsonSerializable
{
    /**
     * @param array<int, array{process: string, label?: string}|string>|array<string, string> $processBlackList
     */
    public function __construct(
        private ?int $processCheckInterval,
        private bool $processCheckOnTestNavigation,
        private array $processBlackList,
        private array $mockActiveProcesses = [],
        private bool $simulateBreach = false
    ) {
    }

    public function getProcessCheckInterval(): ?int
    {
        return $this->processCheckInterval;
    }

    public function isProcessCheckOnTestNavigation(): bool
    {
        return $this->processCheckOnTestNavigation;
    }

    /**
     * @return array<int, array{process: string, label?: string}|string>|array<string, string>
     */
    public function getProcessBlackList(): array
    {
        return $this->processBlackList;
    }

    /**
     * @return list<string>
     */
    public function getMockActiveProcesses(): array
    {
        return $this->mockActiveProcesses;
    }

    public function isSimulateBreach(): bool
    {
        return $this->simulateBreach;
    }

    public function jsonSerialize(): array
    {
        return [
            'processCheckInterval' => $this->processCheckInterval,
            'processCheckOnTestNavigation' => $this->processCheckOnTestNavigation,
            'processBlackList' => $this->processBlackList,
            'mockActiveProcesses' => $this->mockActiveProcesses,
            'simulateBreach' => $this->simulateBreach,
        ];
    }
}
