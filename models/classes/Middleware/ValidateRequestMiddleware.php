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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateRequestMiddleware extends ConfigurableService implements MiddlewareInterface
{
    public const SCHEMA_MAP = 'schema_map';
    public const SERVICE_ID = 'tao/ValidateRequestMiddleware';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->getOpenApiSchemaMapping() as $schema) {
            $validator = (new ValidatorBuilder())->fromYamlFile($schema)->getServerRequestValidator();
            $validator->validate($request);
        }

        return $handler->handle($request);
    }

    private function getOpenApiSchemaMapping(): array
    {
        return $this->getOption(self::SCHEMA_MAP, []);
    }
}
