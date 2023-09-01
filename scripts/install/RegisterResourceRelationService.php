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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\scripts\install;

use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\resources\relation\service\ItemResourceRelationService;
use oat\tao\model\resources\relation\service\ResourceRelationServiceProxy;
use oat\tao\model\session\Business\Service\SessionCookieService;
use oat\tao\model\user\TaoRoles;

class RegisterResourceRelationService extends InstallAction
{
    public function __invoke($params = [])
    {
        AclProxy::applyRule(
            new AccessRule(
                AccessRule::GRANT,
                TaoRoles::BASE_USER,
                [
                    'ext' => 'tao',
                    'mod' => 'ResourceRelations'
                ]
            )
        );

        $resourceRelationServiceProxy = new ResourceRelationServiceProxy();
        $resourceRelationServiceProxy->addService('item', ItemResourceRelationService::SERVICE_ID);

        $serviceManager = $this->getServiceManager();
        $serviceManager->register(ItemResourceRelationService::SERVICE_ID, new ItemResourceRelationService());
        $serviceManager->register(ResourceRelationServiceProxy::SERVICE_ID, $resourceRelationServiceProxy);

        return Report::createSuccess(SessionCookieService::class . ' registered');
    }
}
