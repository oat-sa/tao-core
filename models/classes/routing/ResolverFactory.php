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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\routing;

use common_http_Request;
use Exception;
use oat\oatbox\service\ServiceManager;
use tao_helpers_Uri;
use Throwable;

class ResolverFactory
{
    private ServiceManager $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function create(array $params): Resolver
    {
        $url = tao_helpers_Uri::url($params['action'], $params['module'], $params['extension']);

        try {
            $route = new Resolver(new common_http_Request($url));
            $this->serviceManager->propagate($route);
        } catch (Throwable $exception) {
            throw new Exception(
                __('Wrong or missing parameter extension, module or action'),
                $exception
            );
        }

        return $route;
    }
}
