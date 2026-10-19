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

namespace oat\tao\test\unit\models\classes\lockDownBrowser\Service;

use oat\tao\model\lockDownBrowser\Service\LockDownBrowserClientConfigurationRetriever;
use PHPUnit\Framework\TestCase;

class LockDownBrowserClientConfigurationRetrieverTest extends TestCase
{
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
        $retriever = new LockDownBrowserClientConfigurationRetriever(
            json_encode([
                'processCheckInterval' => 120000,
                'processCheckOnTestNavigation' => false,
                'processBlackList' => [
                    ['process' => 'slack', 'label' => 'Slack'],
                ],
                'mockActiveProcesses' => ['slack'],
                'simulateBreach' => false,
            ], JSON_THROW_ON_ERROR)
        );

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
        $retriever = new LockDownBrowserClientConfigurationRetriever('{"simulateBreach":true}');

        $this->assertTrue($retriever->retrieve()->simulateBreach);
    }

    public function testRetrieveMergesEnvJsonConfig(): void
    {
        $retriever = new LockDownBrowserClientConfigurationRetriever(
            '{"processBlackList":[{"process":"notion","label":"Notion"}]}'
        );

        $config = $retriever->retrieve();

        $this->assertCount(1, $config->processBlackList);
    }

    public function testZeroIntervalBecomesNull(): void
    {
        $retriever = new LockDownBrowserClientConfigurationRetriever('{"processCheckInterval":0}');

        $this->assertNull($retriever->retrieve()->processCheckInterval);
    }
}
