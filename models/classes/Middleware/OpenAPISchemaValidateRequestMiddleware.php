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

use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Context\ContextInterface;
use oat\tao\model\Middleware\Context\OpenApiMiddlewareContext;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OpenAPISchemaValidateRequestMiddleware extends ConfigurableService implements MiddlewareInterface
{
    public const OPTION_SCHEMA_MAP = 'schema_map';
    public const SERVICE_ID = 'tao/OpenAPISchemaValidateRequestMiddleware';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->getApplicableSchemas($request) as $schema) {
            $validator = (new ValidatorBuilder())->fromYamlFile($schema)->getServerRequestValidator();
            $validator->validate($request);
        }

        return $handler->handle($request);
    }

    public function addSchema(ContextInterface $context): self
    {
        $map = array_merge_recursive(
            $this->getOption(self::OPTION_SCHEMA_MAP, []),
            [
                $context->getParameter(OpenApiMiddlewareContext::PARAM_ROUTE) => [
                    $context->getParameter(OpenApiMiddlewareContext::PARAM_SCHEMA_PATH)
                ]
            ]
        );
        
        $this->setOption(self::OPTION_SCHEMA_MAP, $map);

        return $this;
    }

    public function removeSchema(ContextInterface $context): self
    {
        $map = $this->getOption(self::OPTION_SCHEMA_MAP);

        $path = $context->getParameter(OpenApiMiddlewareContext::PARAM_SCHEMA_PATH);
        $route = $context->getParameter(OpenApiMiddlewareContext::PARAM_ROUTE);

        if ($route && !$path) {
            unset($map[$route]);
        }
        if ($path && $route) {
            $routed = $map[$route];
            $key = array_search($path, $routed);
            
            if ($key !== false) {
                unset($routed[$key]);
                $map[$route] = $routed;
            }
        }

        $this->setOption(OpenAPISchemaValidateRequestMiddleware::OPTION_SCHEMA_MAP, array_filter($map));

        return $this;
    }

    private function getApplicableSchemas(RequestInterface $request): array
    {
        return $this->getOption(self::OPTION_SCHEMA_MAP, [])[$request->getUri()->getPath()] ?? [];
    }
}
