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
 * Copyright (c) 2019-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\webhooks\task;

use GuzzleHttp\Psr7\Response;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\task\InvalidJsonException;
use oat\tao\model\webhooks\task\JsonValidator;
use oat\tao\model\webhooks\task\JsonWebhookResponseFactory;

class JsonWebhookResponseFactoryTest extends TestCase
{
    use ServiceManagerMockTrait;

    public function testGetAcceptedContentType()
    {
        $factory = new JsonWebhookResponseFactory();
        self::assertSame('application/json', $factory->getAcceptedContentType());
    }

    public function testCreatePositive()
    {
        $body = [
            'events' => [
                [
                    'eventId' => '52a3de8dd0f270fd193f9f4bff05232f',
                    'status' => 'accepted'
                ]
            ]
        ];

        $factory = new JsonWebhookResponseFactory();

        $jsonValidatorMock = $this->createMock(JsonValidator::class);
        $jsonValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(null)
            ->with($this->callback(function ($body) {
                $event = reset($body->events);
                return $event->eventId === '52a3de8dd0f270fd193f9f4bff05232f' &&
                       $event->status === 'accepted';
            }));

        $factory->setServiceLocator($this->getServiceManagerMock([
            JsonValidator::class => $jsonValidatorMock
        ]));

        $response = new Response(
            200,
            [
                'Content-Type' => $factory->getAcceptedContentType()
            ],
            json_encode($body)
        );

        $parsedResponse = $factory->create($response);
        $this->assertTrue($parsedResponse->isDelivered('52a3de8dd0f270fd193f9f4bff05232f'));
        $this->assertCount(1, $parsedResponse->getStatuses());
        $this->assertSame('accepted', $parsedResponse->getStatus('52a3de8dd0f270fd193f9f4bff05232f'));
        $this->assertNull($parsedResponse->getParseError());
    }

    public function testCreateInvalidContentType()
    {
        $factory = new JsonWebhookResponseFactory();

        $response = new Response(
            200,
            [
                'Content-Type' => 'application/xml'
            ],
            '<xml>'
        );

        $parsedResponse = $factory->create($response);
        $this->assertNotNull($parsedResponse->getParseError());
        $this->assertCount(0, $parsedResponse->getStatuses());
    }

    public function testCreateInvalidData()
    {
        $factory = new JsonWebhookResponseFactory();

        $jsonValidatorMock = $this->createMock(JsonValidator::class);
        $jsonValidatorMock->expects($this->once())
            ->method('validate')
            ->with(['ab'])
            ->willThrowException(new InvalidJsonException('Err', 0, ['err1']));

        $factory->setServiceLocator($this->getServiceManagerMock([
            JsonValidator::class => $jsonValidatorMock
        ]));

        $response = new Response(
            200,
            [
                'Content-Type' => $factory->getAcceptedContentType()
            ],
            '["ab"]'
        );

        $parsedResponse = $factory->create($response);
        $this->assertNotNull($parsedResponse->getParseError());
        $this->assertCount(0, $parsedResponse->getStatuses());
    }
}
