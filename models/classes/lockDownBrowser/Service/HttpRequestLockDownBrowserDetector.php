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

namespace oat\tao\model\lockDownBrowser\Service;

use oat\tao\model\lockDownBrowser\Entity\LockDownBrowserConfiguration;
use Psr\Http\Message\ServerRequestInterface;

final class HttpRequestLockDownBrowserDetector implements LockDownBrowserDetectorInterface
{
    private const REQUEST_PARAM_CLIENT_KIOSK_API_OK = 'clientKioskApiOk';
    private const REQUEST_PARAM_CLIENT_LDB_MOCK = 'ldbMock';

    public function detect(ServerRequestInterface $request): ?string
    {
        if ($this->isUserAgentKiosked($request)) {
            return LockDownBrowserConfiguration::PROVIDER_KIOSKED;
        }

        if ($this->getBooleanRequestValue($request, self::REQUEST_PARAM_CLIENT_LDB_MOCK)) {
            return LockDownBrowserConfiguration::PROVIDER_MOCK;
        }

        return null;
    }

    private function isUserAgentKiosked(ServerRequestInterface $request): bool
    {
        $userAgent = $request->getHeaderLine('User-Agent');

        if ($userAgent === '') {
            return false;
        }

        return preg_match('/kiosk(ed)?/i', $userAgent)
            && $this->getBooleanRequestValue($request, self::REQUEST_PARAM_CLIENT_KIOSK_API_OK);
    }

    private function getBooleanRequestValue(ServerRequestInterface $request, string $param): bool
    {
        $value = $this->getRequestParameter($request, $param);

        if ($value === null) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return mixed|null
     */
    private function getRequestParameter(ServerRequestInterface $request, string $name)
    {
        $query = $request->getQueryParams();

        if (array_key_exists($name, $query)) {
            return $query[$name];
        }

        $parsedBody = $request->getParsedBody();

        if (is_array($parsedBody) && array_key_exists($name, $parsedBody)) {
            return $parsedBody[$name];
        }

        return null;
    }
}
