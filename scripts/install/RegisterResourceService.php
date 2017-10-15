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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts\install;

use common_report_Report as Report;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\resource\ResourceService;
use oat\tao\model\resource\ListResourceLookup;
use oat\tao\model\resource\TreeResourceLookup;

/**
 * Registers the ResourceService
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class RegisterResourceService extends AbstractAction
{
    public function __invoke($params)
    {
        $this->getServiceManager()->register(
            ResourceService::SERVICE_ID, new ResourceService()
        );
        $this->getServiceManager()->register(ListResourceLookup::SERVICE_ID, new ListResourceLookup());
        $this->getServiceManager()->register(TreeResourceLookup::SERVICE_ID, new TreeResourceLookup());
        return new Report(Report::TYPE_SUCCESS, 'Resource services resgistered');
    }
}
