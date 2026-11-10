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

namespace oat\tao\model\browser\Service;

use GuzzleHttp\Psr7\ServerRequest;
use oat\tao\model\browser\Entity\Browser;
use Psr\Http\Message\ServerRequestInterface;
use Sinergi\BrowserDetector\Browser as UserAgentBrowser;

class HttpRequestBrowserDetector
{
    public function __construct(
        private readonly HttpRequestLockDownBrowserDetector $httpRequestLockDownBrowserDetector,
        private readonly ?UserAgentBrowser $browser = null
    ) {
    }

    public function detect(ServerRequestInterface $request = null): Browser
    {
        if ($request === null) {
            $request = ServerRequest::fromGlobals();
        }

        $userAgentBrowser = $this->browser ?? new UserAgentBrowser();
        $lockDownBrowser = $this->httpRequestLockDownBrowserDetector->ignoreClientCheck()->detect($request);

        if ($lockDownBrowser) {
            return $lockDownBrowser;
        }

        return new Browser(
            $userAgentBrowser->getName(),
            $userAgentBrowser->getVersion()
        );
    }
}
