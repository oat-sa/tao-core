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

use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\configEntity\WebhookEntryFactory;
use oat\tao\model\webhooks\configEntity\WebhookInterface;

class WebhookEntryFactoryTest extends TestCase
{
    /** @var WebhookEntryFactory */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new WebhookEntryFactory();
    }

    public function testCreateEntryFromArray()
    {
        $extraPayload = [
            'extra' => 'data'
        ];

        $webhook = $this->subject->createEntryFromArray([
            'id' => 'wh1',
            'url' => 'http://url.com',
            'httpMethod' => 'POST',
            'retryMax' => 5,
            'responseValidation' => true,
            'auth' => [
                'authClass' => 'SomeClass',
                'credentials' => [
                    'p1' => 'v1'
                ]
            ],
            'extraPayload' => $extraPayload
        ]);

        $this->assertInstanceOf(WebhookInterface::class, $webhook);

        $this->assertEquals('wh1', $webhook->getId());
        $this->assertEquals('http://url.com', $webhook->getUrl());
        $this->assertEquals('POST', $webhook->getHttpMethod());
        $this->assertEquals(5, $webhook->getMaxRetries());
        $this->assertEquals('SomeClass', $webhook->getAuth()->getAuthClass());
        $this->assertEquals(['p1' => 'v1'], $webhook->getAuth()->getCredentials());
        $this->assertEquals(true, $webhook->getResponseValidationEnable());
        $this->assertEquals($extraPayload, $webhook->getExtraPayload());
    }

    public function testCreateEntryFromArrayWithoutAuth()
    {
        $webhook = $this->subject->createEntryFromArray([
            'id' => 'wh1',
            'url' => 'http://url.com',
            'httpMethod' => 'POST',
            'retryMax' => 5,
            'responseValidation' => true
        ]);

        $this->assertInstanceOf(WebhookInterface::class, $webhook);

        $this->assertEquals('wh1', $webhook->getId());
        $this->assertEquals('http://url.com', $webhook->getUrl());
        $this->assertEquals('POST', $webhook->getHttpMethod());
        $this->assertEquals(true, $webhook->getResponseValidationEnable());
        $this->assertNull($webhook->getAuth());
    }

    public function testInvalidConfig()
    {
        $this->expectException(\InvalidArgumentException::class);
        try {
            $this->subject->createEntryFromArray([
                'id' => 'wh1',
                'httpMethod' => 123,
                'retryMax' => 5,
                'auth' => [
                    'authClass' => 'SomeClass',
                    'credentials' => [
                        'p1' => 'v1'
                    ]
                ]
            ]);
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('httpMethod', $exception->getMessage());
            $this->assertStringContainsString('url', $exception->getMessage());

            throw $exception;
        }
    }

    public function testInvalidAuthConfig()
    {
        $this->expectException(\InvalidArgumentException::class);

        try {
            $this->subject->createEntryFromArray([
                'id' => 'wh1',
                'url' => 'http://url.com',
                'httpMethod' => 'POST',
                'retryMax' => 5,
                'auth' => [
                    'credentials' => [
                        'p1' => 'v1'
                    ]
                ]
            ]);
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('authClass', $exception->getMessage());

            throw $exception;
        }
    }
}
