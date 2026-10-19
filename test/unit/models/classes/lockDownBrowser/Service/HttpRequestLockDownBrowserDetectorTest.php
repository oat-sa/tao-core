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

namespace oat\tao\test\unit\models\classes\lockDownBrowser\Service;

use GuzzleHttp\Psr7\ServerRequest;
use oat\tao\model\lockDownBrowser\Entity\LockDownBrowserConfiguration;
use oat\tao\model\lockDownBrowser\Service\HttpRequestLockDownBrowserDetector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class HttpRequestLockDownBrowserDetectorTest extends TestCase
{
    private HttpRequestLockDownBrowserDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new HttpRequestLockDownBrowserDetector();
    }

    /**
     * @dataProvider detectProvider
     */
    public function testDetect(
        ?string $expectedProvider,
        string $method,
        string $userAgent,
        array $queryParams,
        array $parsedBody
    ): void {
        $request = $this->createRequest($method, $userAgent, $queryParams, $parsedBody);

        $this->assertSame($expectedProvider, $this->detector->detect($request));
    }

    public function detectProvider(): array
    {
        $kiosked = LockDownBrowserConfiguration::PROVIDER_KIOSKED;
        $mock = LockDownBrowserConfiguration::PROVIDER_MOCK;

        return [
            'real Kiosk{ed}: kiosk user agent and clientKioskApiOk in POST body' => [
                $kiosked,
                'POST',
                'Mozilla Kiosked/1.0',
                [],
                ['clientKioskApiOk' => '1'],
            ],
            'real Kiosk{ed}: clientKioskApiOk in query (GET)' => [
                $kiosked,
                'GET',
                'Mozilla Kiosk/2.0',
                ['clientKioskApiOk' => '1'],
                [],
            ],
            'no session: kiosk user agent but clientKioskApiOk is false' => [
                null,
                'POST',
                'Mozilla Kiosked/1.0',
                [],
                ['clientKioskApiOk' => '0'],
            ],
            'no session: kiosk user agent without clientKioskApiOk' => [
                null,
                'POST',
                'Mozilla Kiosked/1.0',
                [],
                [],
            ],
            'no session: clientKioskApiOk alone does not select mock provider' => [
                null,
                'POST',
                'Mozilla/5.0 Chrome',
                [],
                ['clientKioskApiOk' => '1'],
            ],
            'mock driver: ldbMock in POST body' => [
                $mock,
                'POST',
                'Mozilla/5.0 Chrome',
                [],
                ['ldbMock' => '1'],
            ],
            'mock driver: ldbMock in query (GET)' => [
                $mock,
                'GET',
                'Mozilla/5.0 Chrome',
                ['ldbMock' => '1'],
                [],
            ],
            'mock driver: kiosk UA without clientKioskApiOk falls back to ldbMock' => [
                $mock,
                'POST',
                'Mozilla Kiosked/1.0',
                [],
                ['ldbMock' => '1'],
            ],
        ];
    }

    private function createRequest(
        string $method,
        string $userAgent,
        array $queryParams,
        array $parsedBody
    ): ServerRequestInterface {
        $request = (new ServerRequest($method, '/'))
            ->withHeader('User-Agent', $userAgent)
            ->withQueryParams($queryParams);

        if ($parsedBody !== []) {
            return $request->withParsedBody($parsedBody);
        }

        return $request;
    }
}
