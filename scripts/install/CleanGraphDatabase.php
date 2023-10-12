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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\tao\scripts\install;

use common_persistence_Manager;
use oat\oatbox\extension\InstallAction;

class CleanGraphDatabase extends InstallAction
{
    public function __invoke($params)
    {
        $persistenceId = $params[0];
        if (empty($persistenceId)) {
            throw new \common_Exception('Persistence id should be provided in script parameters');
        }

        $persistence = $this->getServiceManager()
            ->get(common_persistence_Manager::SERVICE_ID)
            ->getPersistenceById($persistenceId);

        $persistence->run('MATCH (n) DETACH DELETE n');
        $persistence->run('CALL apoc.schema.assert({}, {}, true) YIELD label, key RETURN *');
    }
}
