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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\webhooks;

use oat\generis\test\TestCase;
use oat\tao\model\webhooks\EventWebhookConfigFileRepository;

class EventWebhookConfigFileRepositoryTest extends TestCase
{
    /** @var EventWebhookConfigFileRepository */
    private $repository;

    protected function setUp()
    {
        $this->repository = new EventWebhookConfigFileRepository([
            EventWebhookConfigFileRepository::OPTION_EVENTS => [
                'TestEvent' => ['wh1']
            ],
            EventWebhookConfigFileRepository::OPTION_WEBHOOKS => [
                'wh1' => [
                    'id' => 'wh1',
                    'url' => 'http://url.com',
                    'httpMethod' => 'POST',
                    'auth' => [
                        'authClass' => 'SomeClass',
                        'properties' => [
                            'p1' => 'v1'
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testGetWebhookConfig() {
        $whConfig = $this->repository->getWebhookConfig('wh1');

        $this->assertEquals('wh1', $whConfig->getId());
        $this->assertEquals('http://url.com', $whConfig->getUrl());
        $this->assertEquals('POST', $whConfig->getHttpMethod());
        $this->assertEquals('SomeClass', $whConfig->getAuth()->getAuthClass());
        $this->assertEquals(['p1' => 'v1'], $whConfig->getAuth()->getProperties());

        $this->assertNull($this->repository->getWebhookConfig('wh2'));
    }

    public function testGetWebhookConfigIds() {
        $this->assertEquals(['wh1'], $this->repository->getWebhookConfigIds('TestEvent'));
        $this->assertEquals([], $this->repository->getWebhookConfigIds('AnotherEvent'));
    }
}
