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

namespace oat\tao\model\webhooks\log;

use common_persistence_SqlPersistence;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;

abstract class AbstractWebhookLogRepository extends ConfigurableService implements WebhookLogRepositoryInterface
{
    /** @var common_persistence_SqlPersistence|null */
    private $persistence;

    private const OPTION_PERSISTENCE = 'persistence';

    /**
     * @throws InconsistencyConfigException
     */
    protected function getPersistence(): common_persistence_SqlPersistence
    {
        if (!$this->persistence) {
            $persistenceId = $this->getOption(self::OPTION_PERSISTENCE) ?: 'default';
            /** @var PersistenceManager $persistenceManager */
            $persistenceManager = $this->getServiceLocator()->get(PersistenceManager::SERVICE_ID);
            $persistence = $persistenceManager->getPersistenceById($persistenceId);
            if (!$persistence instanceof common_persistence_SqlPersistence) {
                throw new InconsistencyConfigException(
                    "Configured persistence '$persistenceId' is not sql persistence"
                );
            }
            $this->persistence = $persistence;
        }
        return $this->persistence;
    }
}
