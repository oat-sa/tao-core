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
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\TaskOrchestrator;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use oat\tao\model\TaskOrchestrator\TaskOrchestratorClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class TaskOrchestratorClientTest extends TestCase
{
    /** @var GuzzleHttpClient&MockObject */
    private GuzzleHttpClient $httpClient;

    /** @var CacheInterface&MockObject */
    private CacheInterface $cache;

    private TaskOrchestratorClient $sut;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(GuzzleHttpClient::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cache->method('get')->willReturn('cached-access-token');

        $this->sut = new TaskOrchestratorClient(
            'http://to.example',
            'http://auth.example',
            'client-id',
            'client-secret',
            $this->httpClient,
            $this->cache
        );
    }

    public function testSendJobMapsInvalidRequest400ToInvalidArgumentException(): void
    {
        $errorBody = [
            'error' => 'Invalid request',
            'details' => ['email' => 'alice@example.test', 'actorLogin' => 'alice'],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'http://to.example/api/v1/jobs/job-1',
                $this->callback(static function (array $options): bool {
                    return ($options['http_errors'] ?? true) === false
                        && ($options['headers']['Authorization'] ?? null) === 'Bearer cached-access-token';
                })
            )
            ->willReturn(new Response(400, ['Content-Type' => 'application/json'], json_encode($errorBody)));

        try {
            $this->sut->sendJob('job-1', ['type' => 'portalEmailNotification']);
            $this->fail('Expected InvalidArgumentException');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('TO API: request validation failed (HTTP 400)', $exception->getMessage());
            $this->assertStringNotContainsString('alice@example.test', $exception->getMessage());
            $this->assertStringNotContainsString('alice', $exception->getMessage());
        }
    }

    public function testSendJobMapsUnexpected4xxWithoutResponseBodyInMessage(): void
    {
        $errorBody = [
            'error' => 'Forbidden',
            'recipient' => 'bob@example.test',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn(new Response(403, ['Content-Type' => 'application/json'], json_encode($errorBody)));

        try {
            $this->sut->sendJob('job-1', ['type' => 'portalEmailNotification']);
            $this->fail('Expected RuntimeException');
        } catch (RuntimeException $exception) {
            $this->assertSame('TO API: unexpected HTTP 403', $exception->getMessage());
            $this->assertStringNotContainsString('bob@example.test', $exception->getMessage());
        }
    }

    public function testSendJobReturnsDecodedBodyOnSuccess(): void
    {
        $successBody = ['status' => 'ok', 'id' => 'job-1'];

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'http://to.example/api/v1/jobs/job-1',
                $this->callback(static function (array $options): bool {
                    return ($options['http_errors'] ?? true) === false
                        && ($options['headers']['Authorization'] ?? null) === 'Bearer cached-access-token';
                })
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($successBody)));

        $result = $this->sut->sendJob('job-1', ['type' => 'portalEmailNotification']);

        $this->assertSame($successBody, $result);
    }

    public function testSendJobFetchesAccessTokenWhenCacheMisses(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('task_orchestrator_access_token_' . md5('client-id'))
            ->willReturn(null);
        $cache
            ->expects($this->once())
            ->method('set')
            ->with(
                'task_orchestrator_access_token_' . md5('client-id'),
                'fresh-token',
                3540
            );

        $httpClient = $this->createMock(GuzzleHttpClient::class);
        $httpClient
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function (string $method, string $url, array $options = []): Response {
                $this->assertSame('POST', $method);

                if ($url === 'http://auth.example/v1/oauth2/tokens') {
                    $this->assertSame(
                        [
                            'grant_type' => 'client_credentials',
                            'client_id' => 'client-id',
                            'client_secret' => 'client-secret',
                        ],
                        $options['json'] ?? null
                    );

                    return new Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode(['access_token' => 'fresh-token', 'expires_in' => 3600])
                    );
                }

                $this->assertSame('http://to.example/api/v1/jobs/job-2', $url);
                $this->assertSame('Bearer fresh-token', $options['headers']['Authorization'] ?? null);
                $this->assertFalse($options['http_errors'] ?? true);

                return new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['status' => 'accepted'])
                );
            });

        $sut = new TaskOrchestratorClient(
            'http://to.example',
            'http://auth.example',
            'client-id',
            'client-secret',
            $httpClient,
            $cache
        );

        $result = $sut->sendJob('job-2', ['type' => 'portalEmailNotification']);

        $this->assertSame(['status' => 'accepted'], $result);
    }
}
