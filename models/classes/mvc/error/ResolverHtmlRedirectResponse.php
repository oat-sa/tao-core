<?php

declare(strict_types=1);

/*
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 31 Milk St # 960789 Boston, MA 02196 USA.
 *
 *  Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

namespace oat\tao\model\mvc\error;

use common_session_SessionManager;
use oat\tao\model\mvc\DefaultUrlService;

class ResolverHtmlRedirectResponse extends LoginResponse
{
    public function send()
    {
        /** @var DefaultUrlService $urlRouteService */
        $urlRouteService = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);

        header(
            \HTTPToolkit::locationHeader(
                $urlRouteService->getResolverExceptionRedirectUrl(common_session_SessionManager::isAnonymous())
            )
        );
    }
}
