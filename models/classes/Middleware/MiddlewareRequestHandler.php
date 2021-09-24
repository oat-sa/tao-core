<?php

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

declare(strict_types=1);

namespace oat\tao\model\Middleware;

use GuzzleHttp\Psr7\Response;
use LogicException;
use oat\oatbox\service\ConfigurableService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Relay\Relay;

class MiddlewareRequestHandler extends ConfigurableService implements RequestHandlerInterface
{
    public const SERVICE_ID = 'tao/MiddlewareRequestHandler';
    public const OPTION_MAP = 'map';

    /** @var ResponseInterface|null */
    private $originalResponse;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queue = $this->build($request);
        return (new Relay($queue))->handle($request);
    }

    /**
     * @return MiddlewareInterface[]
     */
    private function build(RequestInterface $request): array
    {
        $mapping = [];

        foreach ($this->getMatchedMiddlewareReferences($request) as $middlewareClass) {
            $middleware = $this->getServiceLocator()->get($middlewareClass);
            if (!$middleware instanceof MiddlewareInterface) {
                throw new LogicException(sprintf('Incorrect middleware configuration for %s', $middlewareClass));
            }
            $mapping[] = $middleware;
        }

        return array_merge(
            [
                function ($request, $next) {
                    return $this->originalResponse ?? new Response();
                }
            ],
            $mapping,
            [
                function ($request, $next) {
                    return new Response();
                }
            ]
        );
    }

    public function withOriginalResponse(ResponseInterface $response): self
    {
        $this->originalResponse = $response;
        return $this;
    }

    /**
     * @return string[]
     */
    private function getMatchedMiddlewareReferences(RequestInterface $request): array
    {
        return $this->getOption(self::OPTION_MAP, [])[$request->getUri()->getPath()] ?? [];
    }
}
