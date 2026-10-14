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

namespace oat\tao\test\unit\model\lockDownBrowser;

use oat\tao\model\lockDownBrowser\Service\LockDownBrowserClientConfigurationRetriever;
use PHPUnit\Framework\TestCase;

class LockDownBrowserClientConfigurationRetrieverTest extends TestCase
{
    public function testRetrieveDefaults(): void
    {
        $retriever = new LockDownBrowserClientConfigurationRetriever();

        $config = $retriever->retrieve();

        $this->assertSame(5000, $config->getProcessCheckInterval());
        $this->assertTrue($config->isProcessCheckOnTestNavigation());
        $this->assertCount(1, $config->getProcessBlackList());
        $this->assertSame(['notion'], $config->getMockActiveProcesses());
        $this->assertFalse($config->isSimulateBreach());
    }

    public function testRetrieveSimulateBreachFromEnvJson(): void
    {
        $retriever = new LockDownBrowserClientConfigurationRetriever('{"simulateBreach":true}');

        $this->assertTrue($retriever->retrieve()->isSimulateBreach());
    }

    public function testRetrieveMergesEnvJsonConfig(): void
    {
        $retriever = new LockDownBrowserClientConfigurationRetriever(
            '{"processBlackList":[{"process":"notion","label":"Notion"}]}'
        );

        $config = $retriever->retrieve();

        $this->assertCount(1, $config->getProcessBlackList());
    }

    public function testZeroIntervalBecomesNull(): void
    {
        $retriever = new LockDownBrowserClientConfigurationRetriever('{"processCheckInterval":0}');

        $this->assertNull($retriever->retrieve()->getProcessCheckInterval());
    }
}
