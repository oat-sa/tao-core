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

namespace oat\tao\test\unit\models\classes\accessControl\Service;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use oat\tao\model\accessControl\Service\AccessTokenService;
use oat\tao\model\accessControl\Service\ConfigurationService;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class ConfigurationServiceTest extends TestCase
{
    public function testForceRefreshBypassesCachedConfiguration(): void
    {
        $hadConfigUri = array_key_exists('ENV_CONFIG_URI', $_ENV);
        $previousConfigUri = $_ENV['ENV_CONFIG_URI'] ?? null;
        $_ENV['ENV_CONFIG_URI'] = 'https://config.example';

        try {
            $configuration = [['providerName' => 'wproofreader', 'enabled' => false]];
            $cacheKey = 'https://config.example/api/v1/tenants/tenant-id/configurations/'
                . 'spellchecker.external_providers';
            $cache = $this->createMock(CacheInterface::class);
            $cache
                ->expects(self::never())
                ->method('get');
            $cache
                ->expects(self::once())
                ->method('set')
                ->with($cacheKey, json_encode($configuration), 300);

            $subject = new ConfigurationService(
                $this->createMock(AccessTokenService::class),
                $cache,
                300,
                [
                    'handler' => new MockHandler([
                        new Response(200, [], json_encode(['value' => $configuration])),
                    ]),
                ]
            );

            self::assertSame(
                $configuration,
                $subject->fetchConfiguration('spellchecker.external_providers', 'tenant-id', true)
            );
        } finally {
            if ($hadConfigUri) {
                $_ENV['ENV_CONFIG_URI'] = $previousConfigUri;
            } else {
                unset($_ENV['ENV_CONFIG_URI']);
            }
        }
    }
}
