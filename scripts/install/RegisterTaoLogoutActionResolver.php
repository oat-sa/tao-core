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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 *
 *
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\tao\model\mvc\DefaultUrlModule\TaoLogoutResolver;
use oat\tao\model\mvc\DefaultUrlService;

class RegisterTaoLogoutActionResolver extends InstallAction
{
    public function __invoke($params = [])
    {
        $service = $this->getServiceLocator()->get(DefaultUrlService::SERVICE_ID);
        $options = $service->getOptions();

        $logoutOptions = $options['logout'];
        $logoutRedirect = $logoutOptions['redirect'] ?? [];

        $logoutOptions['redirect'] = [];
        $logoutOptions['redirect']['class'] = TaoLogoutResolver::class;
        $logoutOptions['redirect']['options'] = $logoutRedirect;

        $service->setOption('logout', $logoutOptions);

        $this->getServiceLocator()->register(DefaultUrlService::SERVICE_ID, $service);
    }
}
