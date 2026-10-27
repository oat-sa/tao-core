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

use oat\tao\model\browser\Entity\Browser;
use oat\tao\model\browser\Entity\LockDownBrowserConfiguration;
use Psr\Http\Message\ServerRequestInterface;

class HttpRequestLockDownBrowserDetector
{
    private const REQUEST_PARAM_CLIENT_KIOSK_API_OK = 'clientKioskApiOk';
    private const REQUEST_PARAM_CLIENT_LDB_MOCK = 'ldbMock';

    private bool $ignoreClientCheck = false;

    public function ignoreClientCheck(): self
    {
        $this->ignoreClientCheck = true;

        return $this;
    }

    public function detect(ServerRequestInterface $request): ?Browser
    {
        $kiosked = $this->getUserAgentKiosked($request);

        if (
            $kiosked &&
            (
                $this->ignoreClientCheck ||
                $this->getBooleanRequestValue($request, self::REQUEST_PARAM_CLIENT_KIOSK_API_OK)
            )
        ) {
            $this->ignoreClientCheck = false;

            return $kiosked;
        }

        if ($this->getBooleanRequestValue($request, self::REQUEST_PARAM_CLIENT_LDB_MOCK)) {
            $this->ignoreClientCheck = false;

            return new Browser(LockDownBrowserConfiguration::PROVIDER_MOCK);
        }

        $this->ignoreClientCheck = false;

        return null;
    }

    private function getUserAgentKiosked(ServerRequestInterface $request): ?Browser
    {
        $userAgent = $request->getHeaderLine('User-Agent');

        if ($userAgent === '') {
            return null;
        }

        $isKiosked = (bool) preg_match(
            '/\b(?:kiosked|KioskED-[A-Za-z0-9]+)\/(?<version>\d+(?:\.\d+)*)/i',
            $userAgent,
            $matches
        );

        if ($isKiosked) {
            $version = !empty($matches['version']) ? $matches['version'] : null;

            return new Browser(LockDownBrowserConfiguration::PROVIDER_KIOSKED, $version);
        }

        return null;
    }

    private function getBooleanRequestValue(ServerRequestInterface $request, string $param): bool
    {
        $value = $this->getRequestParameter($request, $param);

        if ($value === null) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function getRequestParameter(ServerRequestInterface $request, string $name): mixed
    {
        $query = $request->getQueryParams();

        if (array_key_exists($name, $query)) {
            return $query[$name];
        }

        $parsedBody = $request->getParsedBody();

        if (is_array($parsedBody) && array_key_exists($name, $parsedBody)) {
            return $parsedBody[$name];
        }

        if (is_object($parsedBody) && property_exists($parsedBody, $name)) {
            return $parsedBody->$name;
        }

        return null;
    }
}
