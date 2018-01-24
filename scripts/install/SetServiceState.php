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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *               
 * 
 */

namespace oat\tao\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\oatbox\log\LoggerAwareTrait;

/**
 * This post-installation script creates the ServiceState persistence.
 */
class SetServiceState extends InstallAction
{
    use LoggerAwareTrait;

    /**
     * The default persistence name.
     */
    const DEFAULT_PERSISTENCE_NAME = 'serviceState';

    /**
     * The default persistence driver.
     */
    const DEFAULT_PERSISTENCE_DRIVER = 'phpfile';

    /**
     * Sets the ServiceState persistence.
     *
     * @param $params
     *
     * @return \common_report_Report
     *
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        /** @var \common_persistence_Manager $persistenceManager */
        $persistenceManager = $this->getServiceManager()->get(\common_persistence_Manager::SERVICE_ID);

        if (!$persistenceManager->hasPersistence(static::DEFAULT_PERSISTENCE_NAME)) {
            $persistenceManager->registerPersistence(
                static::DEFAULT_PERSISTENCE_NAME,
                [
                    'driver' => static::DEFAULT_PERSISTENCE_DRIVER,
                ]
            );
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'ServiceState registered');
    }
}
