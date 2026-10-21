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

namespace oat\tao\test\unit\models\classes\browser\Service;

use oat\tao\model\browser\Service\LockDownBrowserClientConfigurationRetriever;
use PHPUnit\Framework\TestCase;

class LockDownBrowserClientConfigurationRetrieverTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_ENV[LockDownBrowserClientConfigurationRetriever::LOCK_DOWN_BROWSER_CONFIG]);
    }

    protected function tearDown(): void
    {
        unset($_ENV[LockDownBrowserClientConfigurationRetriever::LOCK_DOWN_BROWSER_CONFIG]);

        parent::tearDown();
    }

    public function testRetrieveUsesDefaultConfigWhenEnvJsonIsNull(): void
    {
        $defaults = LockDownBrowserClientConfigurationRetriever::DEFAULT_CONFIG;
        $config = (new LockDownBrowserClientConfigurationRetriever())->retrieve();

        $this->assertSame($defaults['processCheckInterval'], $config->processCheckInterval);
        $this->assertSame($defaults['processCheckOnTestNavigation'], $config->processCheckOnTestNavigation);
        $this->assertSame($defaults['processBlackList'], $config->processBlackList);
        $this->assertSame($defaults['mockActiveProcesses'], $config->mockActiveProcesses);
        $this->assertSame($defaults['simulateBreach'], $config->simulateBreach);
    }

    public function testRetrieveAppliesEnvJsonConfiguration(): void
    {
        $this->mockEnvVar([
            'processCheckInterval' => 120000,
            'processCheckOnTestNavigation' => false,
            'processBlackList' => [
                ['process' => 'slack', 'label' => 'Slack'],
            ],
            'mockActiveProcesses' => ['slack'],
            'simulateBreach' => false,
        ]);

        $retriever = new LockDownBrowserClientConfigurationRetriever();
        $config = $retriever->retrieve();

        $this->assertSame(120000, $config->processCheckInterval);
        $this->assertFalse($config->processCheckOnTestNavigation);
        $this->assertSame(
            [['process' => 'slack', 'label' => 'Slack']],
            $config->processBlackList
        );
        $this->assertSame(['slack'], $config->mockActiveProcesses);
        $this->assertFalse($config->simulateBreach);
    }

    public function testRetrieveSimulateBreachFromEnvJson(): void
    {
        $this->mockEnvVar([ 'simulateBreach' => true ]);
        $retriever = new LockDownBrowserClientConfigurationRetriever();

        $this->assertTrue($retriever->retrieve()->simulateBreach);
    }

    public function testRetrieveMergesEnvJsonConfig(): void
    {
        $this->mockEnvVar([
            'processBlackList' => [
                [
                    "process" => "notion",
                    "label" => "Notion"
                ]
            ]
        ]);

        $retriever = new LockDownBrowserClientConfigurationRetriever();

        $config = $retriever->retrieve();

        $this->assertCount(1, $config->processBlackList);
    }

    public function testZeroIntervalBecomesNull(): void
    {
        $this->mockEnvVar([ 'processCheckInterval' => 0 ]);

        $retriever = new LockDownBrowserClientConfigurationRetriever();

        $this->assertNull($retriever->retrieve()->processCheckInterval);
    }

    private function mockEnvVar(array $config): void
    {
        $_ENV[LockDownBrowserClientConfigurationRetriever::LOCK_DOWN_BROWSER_CONFIG] = json_encode(
            $config,
            JSON_THROW_ON_ERROR
        );
    }
}
