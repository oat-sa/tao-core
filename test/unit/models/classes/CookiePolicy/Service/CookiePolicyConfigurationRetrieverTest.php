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
 * Copyright (c) 2025 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\CookiePolicy\Service;

use InvalidArgumentException;
use oat\tao\model\CookiePolicy\Service\CookiePolicyConfigurationRetriever;
use PHPUnit\Framework\TestCase;

class CookiePolicyConfigurationRetrieverTest extends TestCase
{
    public function testRetrieveWithDefaultConfiguration(): void
    {
        $retriever = new CookiePolicyConfigurationRetriever();
        $configuration = $retriever->retrieve();

        $this->assertSame('https://www.taotesting.com/about/privacy/', $configuration->privacyPolicyUrl);
        $this->assertSame('https://www.taotesting.com/about/privacy/', $configuration->cookiePolicyUrl);
        $this->assertTrue($configuration->display); // Default is now true
    }

    public function testRetrieveWithCustomConfiguration(): void
    {
        $retriever = new CookiePolicyConfigurationRetriever(
            json_encode(
                [
                    'privacyPolicyUrl' => 'https://custom.com/privacy',
                    'cookiePolicyUrl' => 'https://custom.com/cookies',
                ]
            )
        );
        $configuration = $retriever->retrieve();

        $this->assertSame('https://custom.com/privacy', $configuration->privacyPolicyUrl);
        $this->assertSame('https://custom.com/cookies', $configuration->cookiePolicyUrl);
        $this->assertTrue($configuration->display); // Should use default from COOKIE_POLICY_CONFIG_DEFAULT (now true)
    }

    public function testRetrieveWithCustomConfigurationIncludingDisplay(): void
    {
        $retriever = new CookiePolicyConfigurationRetriever(
            json_encode(
                [
                    'privacyPolicyUrl' => 'https://custom.com/privacy',
                    'cookiePolicyUrl' => 'https://custom.com/cookies',
                    'display' => true,
                ]
            )
        );
        $configuration = $retriever->retrieve();

        $this->assertSame('https://custom.com/privacy', $configuration->privacyPolicyUrl);
        $this->assertSame('https://custom.com/cookies', $configuration->cookiePolicyUrl);
        $this->assertTrue($configuration->display);
    }

    public function testRetrieveWithDisplayFalse(): void
    {
        $retriever = new CookiePolicyConfigurationRetriever(
            json_encode(
                [
                    'display' => false,
                ]
            )
        );
        $configuration = $retriever->retrieve();

        $this->assertFalse($configuration->display);
        // URLs should still use defaults
        $this->assertSame('https://www.taotesting.com/about/privacy/', $configuration->privacyPolicyUrl);
        $this->assertSame('https://www.taotesting.com/about/privacy/', $configuration->cookiePolicyUrl);
    }

    public function testCannotRetrieveWithInvalidCustomConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid CookiePolicy JSON configuration: Syntax error');

        $retriever = new CookiePolicyConfigurationRetriever('{invalid}');

        $retriever->retrieve();
    }
}
