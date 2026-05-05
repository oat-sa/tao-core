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
use JsonException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

class ConfigurationService
{
    private AccessTokenService $accessTokenService;
    private CacheInterface $cache;
    private int $cacheTtl;

    public function __construct(AccessTokenService $accessTokenService, CacheInterface $cache, int $cacheTtl = 300)
    {
        $this->accessTokenService = $accessTokenService;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    public function fetchConfiguration(string $configurationKey, ?string $tenantId = null): mixed
    {
        $uri = $_ENV['ENV_CONFIG_URI'] ?? getenv('ENV_CONFIG_URI');
        if (!$uri) {
            throw new RuntimeException('Configuration not found.', 404);
        }
        if (null === $tenantId) {
            $token = $this->accessTokenService->extractAccessTokenPayloadFromRequest();
            $tenantId = $token['tenant_id'];
        }
        $key = "$uri/api/v1/tenants/{$tenantId}/configurations/$configurationKey";
        $value = $this->cache->get($key);
        if ($value) {
            return json_decode($value, true);
        }

        $client = new Client();
        $configRequest = new Request('GET', $key);
        try {
            $response = json_decode(
                $client->send($configRequest)->getBody()->getContents(),
                true,
                flags: JSON_THROW_ON_ERROR
            );
            if (!isset($response['value'])) {
                throw new RuntimeException(
                    "Tenant $tenantId configuration $configurationKey not found.",
                    404
                );
            }
            $this->cache->set($key, json_encode($response['value']), $this->cacheTtl);
            return $response['value'];
        } catch (GuzzleException | JsonException $exception) {
            throw new RuntimeException(
                "Failed to fetch tenant $tenantId configuration $configurationKey. {$exception->getMessage()}",
                424,
                $exception
            );
        }
    }
}
