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

namespace oat\tao\test\unit\models\classes\browser\Service;

use GuzzleHttp\Psr7\ServerRequest;
use oat\tao\model\browser\Entity\LockDownBrowserConfiguration;
use oat\tao\model\browser\Service\HttpRequestBrowserDetector;
use oat\tao\model\browser\Service\HttpRequestLockDownBrowserDetector;
use PHPUnit\Framework\TestCase;
use Sinergi\BrowserDetector\Browser as UserAgentBrowser;

class HttpRequestBrowserDetectorTest extends TestCase
{
    public function testDetectReturnsKioskedBrowserFromLockDownDetector(): void
    {
        $request = (new ServerRequest('GET', '/'))
            ->withHeader('User-Agent', 'Mozilla kiosked/9.8.7');

        $subject = new HttpRequestBrowserDetector(new HttpRequestLockDownBrowserDetector());
        $result = $subject->detect($request);

        $this->assertSame(LockDownBrowserConfiguration::PROVIDER_KIOSKED, $result->name);
        $this->assertSame('9.8.7', $result->version);
    }

    public function testDetectFallsBackToUserAgentBrowser(): void
    {
        $request = (new ServerRequest('GET', '/'))
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome');

        $userAgentBrowser = new FixedUserAgentBrowser('Firefox', '120.0');
        $subject = new HttpRequestBrowserDetector(new HttpRequestLockDownBrowserDetector(), $userAgentBrowser);

        $result = $subject->detect($request);

        $this->assertSame('Firefox', $result->name);
        $this->assertSame('120.0', $result->version);
    }
}

/**
 * @internal
 */
final class FixedUserAgentBrowser extends UserAgentBrowser
{
    public function __construct(
        private readonly string $fixedName,
        private readonly string $fixedVersion
    ) {
    }

    public function getName(): string
    {
        return $this->fixedName;
    }

    public function getVersion(): string
    {
        return $this->fixedVersion;
    }
}
