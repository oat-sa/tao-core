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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

declare(strict_types=1);

namespace oat\tao\model\http;

use Psr\Http\Message\RequestInterface;

class RequestRebuilder
{

    /**
     * Takes care about schema rebuilding for the offloaded request
     * Port rebuilding may be added later ( HTTP_X_FORWARDED_PORT )
     */
    public function rebuild(RequestInterface $request): RequestInterface
    {
        $url = $request->getUri();

        if ('http' === $url->getScheme() && $this->wasRequestForwardedByHttps($request)) {
            $url = $url->withScheme('https');
        }

        return $request->withUri($url);
    }

    private function wasRequestForwardedByHttps(RequestInterface $request): bool
    {
        $https = $request->hasHeader('x-forwarded-proto')
            && $request->getHeader('x-forwarded-proto')[0] === 'https';
        $https = $https || ($request->hasHeader('x-forwarded-ssl')
                && $request->getHeader('x-forwarded-ssl')[0] === 'on');

        return $https;
    }
}
