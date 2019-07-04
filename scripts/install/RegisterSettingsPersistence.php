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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\scripts\install;

use Exception;
use common_persistence_Manager;
use common_persistence_SqlKvDriver;
use common_report_Report;
use oat\oatbox\extension\InstallAction;

/**
 * Class RegisterResourceWatcherService
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class RegisterSettingsPersistence extends InstallAction
{

    /**
     * @inheritdoc
     */
    public function __invoke($params)
    {
        try {
            /** @var common_persistence_Manager $persistenceManager */
            $persistenceManager = $this->getServiceLocator()->get(common_persistence_Manager::SERVICE_ID);
            if ($persistenceManager->hasPersistence('default_kv')) {
                return common_report_Report::createInfo('"default_kv" persistence is used by default.');
            } else {
                $persistenceConfig = [
                    'driver' => common_persistence_SqlKvDriver::class,
                    common_persistence_SqlKvDriver::OPTION_PERSISTENCE_SQL => 'default'
                ];
                $persistenceManager->registerPersistence('default_kv', $persistenceConfig);
                $this->getServiceManager()->register(common_persistence_Manager::SERVICE_ID, $persistenceManager);

                return common_report_Report::createInfo('RDS KeyValue implementation was registered as "default_kv" persistence');
            }
        } catch (Exception $e) {
            $this->logError($e->getMessage());

            return common_report_Report::createFailure($e->getMessage());
        }
    }
}
