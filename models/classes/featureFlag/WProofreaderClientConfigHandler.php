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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\featureFlag;

use oat\oatbox\session\SessionService;
use oat\tao\model\tenantConfiguration\Service\ConfigurationService;
use oat\tao\model\user\TaoRoles;
use RuntimeException;

class WProofreaderClientConfigHandler implements FeatureFlagConfigHandlerInterface
{
    private const CONFIGURATION_KEY = 'spellchecker.external_providers';
    private const CLIENT_MODULE = 'tao/ckeditor/wproofreaderBootstrap';
    private const PROVIDER_NAME = 'wproofreader';

    public function __construct(
        private ConfigurationService $configurationService,
        private SessionService $sessionService
    ) {
    }

    public function __invoke(array $configs): array
    {
        if (
            !isset($configs[self::CLIENT_MODULE])
            || $this->sessionService->isAnonymous()
            || !in_array(TaoRoles::BACK_OFFICE, $this->sessionService->getCurrentSession()->getUserRoles(), true)
        ) {
            return $configs;
        }

        try {
            $providers = $this->configurationService->fetch(
                self::CONFIGURATION_KEY,
                $this->sessionService->getTenantId()
            );
        } catch (RuntimeException) {
            return $configs;
        }

        if ($providers === null) {
            return $configs;
        }

        $provider = null;
        if (is_array($providers)) {
            foreach ($providers as $candidate) {
                if (is_array($candidate) && ($candidate['providerName'] ?? null) === self::PROVIDER_NAME) {
                    $provider = $candidate;
                    break;
                }
            }
        }

        $providerConfig = is_array($provider['config'] ?? null) ? $provider['config'] : [];
        $serviceId = is_string($providerConfig['serviceId'] ?? null)
            ? trim($providerConfig['serviceId'])
            : '';

        if (($provider['enabled'] ?? null) !== true || $serviceId === '') {
            $configs[self::CLIENT_MODULE]['spellCheckConfig'] = [
                'enabled' => true,
                'providerId' => 'native',
                'providerConfig' => [],
            ];

            return $configs;
        }

        $installedProviderConfig = $configs[self::CLIENT_MODULE]['spellCheckConfig']['providerConfig'] ?? [];
        if (!is_array($installedProviderConfig)) {
            $installedProviderConfig = [];
        }

        $configs[self::CLIENT_MODULE]['spellCheckConfig'] = [
            'enabled' => true,
            'providerId' => self::PROVIDER_NAME,
            'providerConfig' => array_replace($installedProviderConfig, ['serviceId' => $serviceId]),
        ];

        return $configs;
    }
}
