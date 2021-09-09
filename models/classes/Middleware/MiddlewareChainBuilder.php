<?php

declare(strict_types=1);
/*
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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA
 */

namespace oat\tao\model\Middleware;

use GuzzleHttp\Psr7\Response;
use LogicException;
use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareChainBuilder extends ConfigurableService
{
    public const SERVICE_ID = 'tao/MiddlewareChainBuilder';
    public const MAP = 'map';

    /**
     * @return MiddlewareInterface[]
     */
    public function build(RequestInterface $request): array
    {
        $path = $request->getUri()->getPath();

        $mapping = [];

        $middlewareReferences = $this->getOption(self::MAP, [])[$path] ?? [];
        foreach ($middlewareReferences as $middlewareClass) {
            $middleware = $this->getServiceLocator()->get($middlewareClass);
            if (!$middleware instanceof MiddlewareInterface) {
                throw new LogicException(sprintf('Incorrect middleware configuration for %s', $middlewareClass));
            }
            $mapping[] = $middleware;
        }

        return array_merge($mapping, [
            function ($request, $next) {
                return new Response();
            }
        ]);
    }
}
