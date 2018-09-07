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
 * Copyright (c) 2014-2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\install;


use oat\oatbox\extension\InstallAction;
use oat\tao\model\requiredAction\implementation\RequiredActionRedirectUrlPart;
use oat\tao\model\requiredAction\implementation\RequiredActionService;
use oat\tao\model\routing\Resolver;

class UpdateRequiredActionUrl extends InstallAction
{
    public function __invoke($params)
    {
        $requiredActionService = $this->getServiceManager()->get(RequiredActionService::CONFIG_ID);
        $actions = $requiredActionService->getOption(RequiredActionService::OPTION_REQUIRED_ACTIONS);
        $updated = false;
        foreach ($actions as $key => $action) {
            if (is_a($action, 'oat\tao\model\requiredAction\implementation\RequiredActionRedirect')) {
                $request = new Resolver(new \common_http_Request($action->getUrl()));
                $this->propagate($request);
                $actions[$key] = new RequiredActionRedirectUrlPart(
                    $action->getName(),
                    $action->getRules(),
                    array(
                        $request->getMethodName(),
                        $request->getControllerShortName(),
                        $request->getExtensionId(),
                    )
                );
                $updated = true;
            }
        }

        if ($updated) {
            $requiredActionService->setOption(RequiredActionService::OPTION_REQUIRED_ACTIONS, $actions);
            $this->getServiceManager()->register(RequiredActionService::CONFIG_ID, $requiredActionService);
        }
    }

}