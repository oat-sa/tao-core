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

namespace oat\tao\test\unit\model\lockDownBrowser;

use GuzzleHttp\Psr7\ServerRequest;
use oat\tao\model\lockDownBrowser\Entity\LockDownBrowser;
use oat\tao\model\lockDownBrowser\Service\HttpRequestLockDownBrowserDetector;
use PHPUnit\Framework\TestCase;

class HttpRequestLockDownBrowserDetectorTest extends TestCase
{
    public function testDetectReturnsKioskedWhenUaAndClientOk(): void
    {
        $request = (new ServerRequest('POST', '/'))
            ->withHeader('User-Agent', 'Mozilla Kiosked/1.0')
            ->withParsedBody(['clientKioskApiOk' => '1']);

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_KIOSKED, $detector->detect($request));
    }

    public function testDetectReturnsNoneWhenClientNotOk(): void
    {
        $request = (new ServerRequest('POST', '/'))
            ->withHeader('User-Agent', 'Mozilla Kiosked/1.0')
            ->withParsedBody(['clientKioskApiOk' => '0']);

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_NONE, $detector->detect($request));
    }

    public function testDetectReturnsNoneWhenKioskUaWithoutClientOk(): void
    {
        $request = (new ServerRequest('POST', '/'))
            ->withHeader('User-Agent', 'Mozilla Kiosked/1.0');

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_NONE, $detector->detect($request));
    }

    public function testDetectReturnsKioskedMockWhenClientOkWithoutKioskUa(): void
    {
        $request = (new ServerRequest('POST', '/'))
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome')
            ->withParsedBody(['clientKioskApiOk' => '1']);

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_KIOSKED_MOCK, $detector->detect($request));
    }

    public function testDetectReadsClientOkFromQueryParams(): void
    {
        $request = (new ServerRequest('GET', '/'))
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome')
            ->withQueryParams(['clientKioskApiOk' => '1']);

        $detector = new HttpRequestLockDownBrowserDetector();

        $this->assertSame(LockDownBrowser::PROVIDER_KIOSKED_MOCK, $detector->detect($request));
    }
}
