<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\accessControl\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class AccessTokenService
{
    private CacheInterface $cache;
    private int $cacheMargin;
    private array $clientConfig;

    /**
     * @see Client
     * @param array $clientConfig
     */
    public function __construct(
        CacheInterface $cache,
        int $cacheMargin = 60,
        array $clientConfig = ['timeout' => 5.0, 'connect_timeout' => 2.0]
    ) {
        $this->clientConfig = $clientConfig;
        $this->cache = $cache;
        $this->cacheMargin = $cacheMargin;
    }

    public function fetchTokens(): array
    {
        $authUri = $_ENV['ENV_AUTH_URI'] ?? getenv('ENV_AUTH_URI');
        $clientId = $_ENV['ENV_CLIENT_ID'] ?? getenv('ENV_CLIENT_ID');
        $clientSecret = $_ENV['ENV_CLIENT_SECRET'] ?? getenv('ENV_CLIENT_SECRET');

        if (!$authUri || !$clientId || !$clientSecret) {
            throw new RuntimeException('OAuth2 credentials not found.', 404);
        }
        $key = "$authUri/$clientId";
        $value = $this->cache->get($key);
        if ($value) {
            return json_decode($value, true);
        }

        $client = new Client($this->clientConfig);
        $request = new Request('POST', "$authUri?with-refresh-token=true", [], json_encode([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret
        ]));
        $request = $request->withAddedHeader('Content-Type', 'application/json');

        try {
            $response = $client->send($request);
        } catch (GuzzleException $exception) {
            throw new RuntimeException('Failed to fetch Auth tokens.', 424, $exception);
        }

        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();
        $payload = json_decode($content, true);

        if ($statusCode !== 200 || !isset($payload['access_token'])) {
            throw new RuntimeException('Failed to fetch Auth tokens.', 424);
        }
        $accessToken = $this->parseAccessToken($payload['access_token']);
        $cacheTtl = (int)($accessToken['exp'] ?? 0) - time() - $this->cacheMargin;
        if ($cacheTtl > 0) {
            $this->cache->set($key, $content, $cacheTtl);
        }

        return $payload;
    }

    public function extractAccessTokenFromRequest(): string
    {
        $authorizationHeader = explode(' ', $_SERVER['HTTP_AUTHORIZATION'] ?? '', 2);
        return array_pop($authorizationHeader);
    }

    public function extractAccessTokenPayloadFromRequest(): array
    {
        return $this->parseAccessToken(
            $this->extractAccessTokenFromRequest()
        );
    }

    public function parseAccessToken(string $accessToken): array
    {
        @[$_, $payload] = explode('.', $accessToken);
        $rawToken = base64_decode(strtr($payload ?? '', '-_', '+/'));
        $token = json_decode($rawToken, true);
        if (empty($token['tenant_id'])) {
            throw new RuntimeException('Unauthorized', 401);
        }
        return $token;
    }
}
