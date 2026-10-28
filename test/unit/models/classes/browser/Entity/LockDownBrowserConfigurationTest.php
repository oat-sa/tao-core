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

namespace oat\tao\test\unit\models\classes\browser\Entity;

use oat\tao\model\browser\Entity\LockDownBrowserConfiguration;
use PHPUnit\Framework\TestCase;

class LockDownBrowserConfigurationTest extends TestCase
{
    public function testGetLockDownBrowserConfiguration(): void
    {
        $processCheckInterval = 120000;
        $processCheckOnTestNavigation = false;
        $processBlackList = [['process' => 'slack', 'label' => 'Slack']];
        $mockActiveProcesses = ['slack'];
        $simulateBreach = true;

        $configuration = new LockDownBrowserConfiguration(
            $processCheckInterval,
            $processCheckOnTestNavigation,
            $processBlackList,
            $mockActiveProcesses,
            $simulateBreach
        );

        $this->assertSame($processCheckInterval, $configuration->processCheckInterval);
        $this->assertSame($processCheckOnTestNavigation, $configuration->processCheckOnTestNavigation);
        $this->assertSame($processBlackList, $configuration->processBlackList);
        $this->assertSame($mockActiveProcesses, $configuration->mockActiveProcesses);
        $this->assertSame($simulateBreach, $configuration->simulateBreach);
        $this->assertSame(
            [
                'processCheckInterval' => $processCheckInterval,
                'processCheckOnTestNavigation' => $processCheckOnTestNavigation,
                'processBlackList' => $processBlackList,
                'mockActiveProcesses' => $mockActiveProcesses,
                'simulateBreach' => $simulateBreach,
            ],
            $configuration->jsonSerialize()
        );
    }

    public function testOptionalConstructorDefaults(): void
    {
        $configuration = new LockDownBrowserConfiguration(null, true, []);

        $this->assertSame([], $configuration->mockActiveProcesses);
        $this->assertFalse($configuration->simulateBreach);
        $this->assertSame(
            [
                'processCheckInterval' => null,
                'processCheckOnTestNavigation' => true,
                'processBlackList' => [],
                'mockActiveProcesses' => [],
                'simulateBreach' => false,
            ],
            $configuration->jsonSerialize()
        );
    }

    /**
     * @dataProvider isProviderAllowedProvider
     */
    public function testIsProviderAllowed(string $provider, bool $expected): void
    {
        $this->assertSame($expected, LockDownBrowserConfiguration::isProviderAllowed($provider));
    }

    public function isProviderAllowedProvider(): array
    {
        return [
            'kiosked' => [LockDownBrowserConfiguration::PROVIDER_KIOSKED, true],
            'mock' => [LockDownBrowserConfiguration::PROVIDER_MOCK, true],
            'unknown' => ['chrome', false],
        ];
    }
}
