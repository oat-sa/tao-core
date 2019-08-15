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
use oat\tao\model\webhooks\configEntity\WebhookEntryFactory;
use oat\tao\model\webhooks\configEntity\WebhookInterface;

class WebhookEntryFactoryTest extends TestCase
{
    public function testCreateEntryFromArray()
    {
        $factory = new WebhookEntryFactory();
        $webhook = $factory->createEntryFromArray([
            'id' => 'wh1',
            'url' => 'http://url.com',
            'httpMethod' => 'POST',
            'auth' => [
                'authClass' => 'SomeClass',
                'credentials' => [
                    'p1' => 'v1'
                ]
            ]
        ]);

        $this->assertInstanceOf(WebhookInterface::class, $webhook);

        $this->assertEquals('wh1', $webhook->getId());
        $this->assertEquals('http://url.com', $webhook->getUrl());
        $this->assertEquals('POST', $webhook->getHttpMethod());
        $this->assertEquals('SomeClass', $webhook->getAuth()->getAuthClass());
        $this->assertEquals(['p1' => 'v1'], $webhook->getAuth()->getCredentials());
    }

    public function testCreateEntryFromArrayWithoutAuth()
    {
        $factory = new WebhookEntryFactory();
        $webhook = $factory->createEntryFromArray([
            'id' => 'wh1',
            'url' => 'http://url.com',
            'httpMethod' => 'POST',
        ]);

        $this->assertInstanceOf(WebhookInterface::class, $webhook);

        $this->assertEquals('wh1', $webhook->getId());
        $this->assertEquals('http://url.com', $webhook->getUrl());
        $this->assertEquals('POST', $webhook->getHttpMethod());
        $this->assertNull($webhook->getAuth());
    }

    public function testInvalidConfig()
    {
        $factory = new WebhookEntryFactory();
        $this->expectException(\InvalidArgumentException::class);
        try {
            $factory->createEntryFromArray([
                'id' => 'wh1',
                'httpMethod' => 123,
                'auth' => [
                    'authClass' => 'SomeClass',
                    'credentials' => [
                        'p1' => 'v1'
                    ]
                ]
            ]);
        }
        catch (\InvalidArgumentException $exception) {
            $this->assertContains('httpMethod', $exception->getMessage());
            $this->assertContains('url', $exception->getMessage());
            throw $exception;
        }
    }

    public function testInvalidAuthConfig()
    {
        $factory = new WebhookEntryFactory();
        $this->expectException(\InvalidArgumentException::class);
        try {
            $factory->createEntryFromArray([
                'id' => 'wh1',
                'url' => 'http://url.com',
                'httpMethod' => 'POST',
                'auth' => [
                    'credentials' => [
                        'p1' => 'v1'
                    ]
                ]
            ]);
        }
        catch (\InvalidArgumentException $exception) {
            $this->assertContains('authClass', $exception->getMessage());
            throw $exception;
        }
    }
}
