<?php

declare(strict_types=1);

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
use oat\tao\model\webhooks\configEntity\WebhookEntryFactory;
use oat\tao\model\webhooks\configEntity\WebhookInterface;

class WebhookEntryFactoryTest extends TestCase
{
    public function testCreateEntryFromArray(): void
    {
        $factory = new WebhookEntryFactory();
        $webhook = $factory->createEntryFromArray([
            'id' => 'wh1',
            'url' => 'http://url.com',
            'httpMethod' => 'POST',
            'retryMax' => 5,
            'auth' => [
                'authClass' => 'SomeClass',
                'credentials' => [
                    'p1' => 'v1',
                ],
            ],
        ]);

        $this->assertInstanceOf(WebhookInterface::class, $webhook);

        $this->assertSame('wh1', $webhook->getId());
        $this->assertSame('http://url.com', $webhook->getUrl());
        $this->assertSame('POST', $webhook->getHttpMethod());
        $this->assertSame(5, $webhook->getMaxRetries());
        $this->assertSame('SomeClass', $webhook->getAuth()->getAuthClass());
        $this->assertSame(['p1' => 'v1'], $webhook->getAuth()->getCredentials());
    }

    public function testCreateEntryFromArrayWithoutAuth(): void
    {
        $factory = new WebhookEntryFactory();
        $webhook = $factory->createEntryFromArray([
            'id' => 'wh1',
            'url' => 'http://url.com',
            'httpMethod' => 'POST',
            'retryMax' => 5,
        ]);

        $this->assertInstanceOf(WebhookInterface::class, $webhook);

        $this->assertSame('wh1', $webhook->getId());
        $this->assertSame('http://url.com', $webhook->getUrl());
        $this->assertSame('POST', $webhook->getHttpMethod());
        $this->assertNull($webhook->getAuth());
    }

    public function testInvalidConfig(): void
    {
        $factory = new WebhookEntryFactory();
        $this->expectException(\InvalidArgumentException::class);
        try {
            $factory->createEntryFromArray([
                'id' => 'wh1',
                'httpMethod' => 123,
                'retryMax' => 5,
                'auth' => [
                    'authClass' => 'SomeClass',
                    'credentials' => [
                        'p1' => 'v1',
                    ],
                ],
            ]);
        } catch (\InvalidArgumentException $exception) {
            $this->assertContains('httpMethod', $exception->getMessage());
            $this->assertContains('url', $exception->getMessage());
            throw $exception;
        }
    }

    public function testInvalidAuthConfig(): void
    {
        $factory = new WebhookEntryFactory();
        $this->expectException(\InvalidArgumentException::class);
        try {
            $factory->createEntryFromArray([
                'id' => 'wh1',
                'url' => 'http://url.com',
                'httpMethod' => 'POST',
                'retryMax' => 5,
                'auth' => [
                    'credentials' => [
                        'p1' => 'v1',
                    ],
                ],
            ]);
        } catch (\InvalidArgumentException $exception) {
            $this->assertContains('authClass', $exception->getMessage());
            throw $exception;
        }
    }
}
