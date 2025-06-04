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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\CookiePolicy\Service;

use oat\tao\model\CookiePolicy\Entity\CookiePolicyConfiguration;

class CookiePolicyConfigurationRetriever
{
    public const COOKIE_POLICY_CONFIG = 'COOKIE_POLICY_CONFIG';
    public const COOKIE_POLICY_CONFIG_DEFAULT = [
        'privacyPolicyUrl' => 'https://www.taotesting.com/about/privacy/',
        'cookiePolicyUrl' => 'https://www.taotesting.com/about/privacy/',
    ];

    private ?string $cookiePolicyJsonConfig;

    public function __construct(?string $cookiePolicyJsonConfig = null)
    {
        $this->cookiePolicyJsonConfig = $cookiePolicyJsonConfig;
    }

    public function retrieve(): CookiePolicyConfiguration
    {
        $config = self::COOKIE_POLICY_CONFIG_DEFAULT;

        if (!empty($this->cookiePolicyJsonConfig)) {
            $config = array_merge($config, json_decode($this->cookiePolicyJsonConfig, true));
        }

        return new CookiePolicyConfiguration($config['privacyPolicyUrl'], $config['cookiePolicyUrl']);
    }
}
