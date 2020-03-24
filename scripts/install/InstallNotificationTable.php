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
 *
 */

namespace oat\tao\scripts\install;

use oat\generis\persistence\sql\SchemaProviderInterface;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\notification\implementation\NotificationServiceAggregator;
use oat\tao\model\notification\implementation\RdsNotificationService;
use oat\tao\model\notification\NotificationServiceInterface;
use oat\generis\persistence\PersistenceManager;

/**
 * Class InstallNotificationTable
 *
 * @deprecated This class is used by client only. It will be moved to client specific extension
 */
class InstallNotificationTable extends InstallAction
{

    public function __invoke($params)
    {
        $notificationService = $this->getServiceLocator()->get(NotificationServiceInterface::SERVICE_ID);
        $schemaCollection = $this->getPersistenceManager()->getSqlSchemas();
        if ($notificationService instanceof SchemaProviderInterface) {
            $notificationService->provideSchema($schemaCollection);
        }
        $this->getPersistenceManager()->applySchemas($schemaCollection);
    }

    /**
     * @return PersistenceManager
     */
    private function getPersistenceManager()
    {
        return $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
    }
}
