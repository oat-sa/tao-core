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

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\Middleware\Context\MiddlewareContext;

class MiddlewareManager extends ConfigurableService
{
    public function append(ContextInterface $context): self
    {
        $middlewareId = $context->getParameter(MiddlewareContext::PARAM_MIDDLEWARE_ID);
        $route = $context->getParameter(MiddlewareContext::PARAM_ROUTE);

        $map = $this->getMiddlewareHandler()->getOption(MiddlewareRequestHandler::OPTION_MAP);

        $this->getMiddlewareHandler()->setOption(
            MiddlewareRequestHandler::OPTION_MAP,
            array_merge_recursive($map, [$route => [$middlewareId]])
        );

        return $this;
    }

    public function detach(ContextInterface $context): self
    {
        $middlewareId = $context->getParameter(MiddlewareContext::PARAM_MIDDLEWARE_ID);
        $route = $context->getParameter(MiddlewareContext::PARAM_ROUTE);

        $map = $this->getMiddlewareHandler()->getOption(MiddlewareRequestHandler::OPTION_MAP);

        if ($middlewareId && $route) {
            $routed = $map[$route];
            if (($key = array_search(
                    $middlewareId,
                    $routed
                )) !== false) {
                unset($routed[$key]);
                $map[$route] = $routed;
            }
        }

        if ($route && !$middlewareId) {
            unset($map[$route]);
        }

        $this->getMiddlewareHandler()->setOption(MiddlewareRequestHandler::OPTION_MAP, array_filter($map));
        return $this;
    }

    public function getMiddlewareHandler(): MiddlewareRequestHandler
    {
        if ($this->getServiceManager()->has(MiddlewareRequestHandler::SERVICE_ID)) {
            return $this->getServiceManager()->get(MiddlewareRequestHandler::SERVICE_ID);
        }
        return new MiddlewareRequestHandler();
    }
}
