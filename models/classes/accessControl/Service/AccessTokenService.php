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
use RuntimeException;

class AccessTokenService
{
    private array $clientConfig;

    /**
     * @see Client
     * @param array $clientConfig
     */
    public function __construct(array $clientConfig = ['timeout' => 5.0, 'connect_timeout' => 2.0])
    {
        $this->clientConfig = $clientConfig;
    }

    public function fetchTokens(): array
    {
        $authUri = getEnv('ENV_AUTH_URI');
        $clientId = getEnv('ENV_CLIENT_ID');
        $clientSecret = getEnv('ENV_CLIENT_SECRET');

        if (!$authUri || !$clientId || !$clientSecret) {
            throw new RuntimeException('OAuth2 credentials not found.', 404);
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
        $content = json_decode($content, true);

        if ($statusCode !== 200 || !isset($content['access_token'])) {
            throw new RuntimeException('Failed to fetch Auth tokens.', 424);
        }

        return $content;
    }

    public function extractAccessTokenFromRequest(): string
    {
        $authorizationHeader = explode(' ', $_SERVER['HTTP_AUTHORIZATION'] ?? '', 2);
        return array_pop($authorizationHeader);
    }

    public function extractAccessTokenPayloadFromRequest(): array
    {
        @[$_, $payload] = explode('.', $this->extractAccessTokenFromRequest());
        $rawToken = base64_decode(strtr($payload ?? '', '-_', '+/'));
        $token = json_decode($rawToken, true);
        if (empty($token['tenant_id'])) {
            throw new RuntimeException('Unauthorized', 401);
        }
        return $token;
    }
}
