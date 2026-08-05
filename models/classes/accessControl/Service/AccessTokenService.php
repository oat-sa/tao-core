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

use common_session_SessionManager as SessionManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use oat\generis\model\user\UserRdf;
use oat\oatbox\user\UserService;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class AccessTokenService
{
    private UserService $userService;
    private Client $client;
    private CacheInterface $cache;
    private float $ttlScale;

    /**
     * @see Client
     */
    public function __construct(
        UserService $userService,
        CacheInterface $cache,
        float $ttlScale = 0.5,
        array $clientConfig = ['timeout' => 5.0, 'connect_timeout' => 2.0]
    ) {
        $this->userService = $userService;
        $this->client = new Client($clientConfig);
        $this->cache = $cache;
        $this->ttlScale = $ttlScale;
    }

    public function fetchTokens(string $role = ''): array
    {
        $authUri = $_ENV['ENV_AUTH_URI'] ?? getenv('ENV_AUTH_URI');
        $clientId = $_ENV['ENV_CLIENT_ID'] ?? getenv('ENV_CLIENT_ID');
        $clientSecret = $_ENV['ENV_CLIENT_SECRET'] ?? getenv('ENV_CLIENT_SECRET');

        if (!$authUri || !$clientId || !$clientSecret) {
            throw new RuntimeException('OAuth2 credentials not found.', 404);
        }
        $userId = $this->getUserLogin();
        $key = "$authUri/$clientId/$userId/$role";
        $value = $this->cache->get($key);
        if ($value) {
            return json_decode($value, true);
        }

        $request = new Request(
            'POST',
            sprintf(
                "%s?%s",
                $authUri,
                http_build_query(array_filter([
                    'with-refresh-token' => true,
                    'with-user-identifier' => SessionManager::buildUserIdentityString($userId, $role),
                    'with-user-role' => 'ROLE_LTI_USER', // required to force a User ID
                ]), arg_separator: '&'),
            ),
            [],
            json_encode([
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ])
        );
        $request = $request->withAddedHeader('Content-Type', 'application/json');

        try {
            $response = $this->client->send($request);
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
        $cacheTtl = (int)(((int)($accessToken['exp'] ?? 0) - time()) * $this->ttlScale);
        if ($cacheTtl > 0) {
            $this->cache->set($key, $content, $cacheTtl);
        }

        return $payload;
    }

    public function extractAccessTokenFromRequest(): string
    {
        return SessionManager::extractAccessTokenFromRequest();
    }

    public function extractAccessTokenPayloadFromRequest(): array
    {
        return $this->parseAccessToken(
            $this->extractAccessTokenFromRequest()
        );
    }

    public function parseAccessToken(string $accessToken): array
    {
        $token = SessionManager::parseAccessToken($accessToken);
        if (empty($token['tenant_id'])) {
            throw new RuntimeException('Unauthorized', 401);
        }
        if ($token['tenant_id'] !== $this->getTenantId()) {
            throw new RuntimeException('Not found', 404);
        }
        return $token;
    }

    private function getTenantId(): string
    {
        $tenantId = $_ENV['TENANT_ID'] ?? getenv('TENANT_ID');
        if (!$tenantId) {
            throw new RuntimeException('Tenant configuration not found.', 404);
        }
        return $tenantId;
    }

    private function getUserLogin(): ?string
    {
        $user = SessionManager::getSession()->getUser();
        $login = current($user->getPropertyValues(UserRdf::PROPERTY_LOGIN));

        if (!$login) {
            $generisUser = $this->userService->getUser($user->getIdentifier());

            $login = current($generisUser->getPropertyValues(UserRdf::PROPERTY_LOGIN));
        }

        return $login ?: null;
    }
}
