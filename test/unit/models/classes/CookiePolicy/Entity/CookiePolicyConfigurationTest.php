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
 * Copyright (c) 2025-2026 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\CookiePolicy\Entity;

use oat\tao\model\CookiePolicy\Entity\CookiePolicyConfiguration;
use PHPUnit\Framework\TestCase;

class CookiePolicyConfigurationTest extends TestCase
{
    public function testGetCookiePolicyConfiguration(): void
    {
        $privacyPolicyLink = 'https://example.com/privacy';
        $cookiePolicyLink = 'https://example.com/cookies';
        $display = true;

        $configuration = new CookiePolicyConfiguration($privacyPolicyLink, $cookiePolicyLink, $display);

        $this->assertSame($privacyPolicyLink, $configuration->privacyPolicyLink);
        $this->assertSame($cookiePolicyLink, $configuration->cookiePolicyLink);
        $this->assertSame($display, $configuration->display);
        $this->assertSame(
            [
                'privacyPolicyLink' => $privacyPolicyLink,
                'cookiePolicyLink' => $cookiePolicyLink,
                'display' => $display,
            ],
            $configuration->jsonSerialize()
        );
    }
}
