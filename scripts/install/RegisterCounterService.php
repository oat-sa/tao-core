<?php /** @noinspection PhpUnhandledExceptionInspection */
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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types = 1);

namespace oat\tao\scripts\install;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\counter\CounterService;

class RegisterCounterService extends InstallAction
{
    private const PERSISTENCE_PRECEDENCE = ['redis', 'default_kv'];

    public function __invoke($params = [])
    {
        $persistence = $this->discoverPersistenceId();

        $this->registerService(
            CounterService::SERVICE_ID,
            new CounterService([
               CounterService::OPTION_PERSISTENCE => $persistence,
               CounterService::OPTION_COUNTER_KEY_PREFIX => CounterService::DEFAULT_PREFIX,
               CounterService::OPTION_EVENTS => []
           ])
        );

        $this->logInfo(
            sprintf(
                "Counter Service registered with persistence '%s' and key prefix '%s'",
                $persistence,
                CounterService::DEFAULT_PREFIX
            )
        );
    }

    /**
     * Search for a suitable persistence. In case of an installation by Seed, or an Update
     * of an existing infrastructure on the OAT ecosystem, we might find the best fit.
     *
     * 1. Most popular Key Value persistence ID in OAT ecosystem is 'redis'. This is the best fit.
     * 2. As a fail-over, 'default_kv' persistence is implemented in all TAO Setups (See generis/manifest.php's
     * Registered Installation Actions).
     */
    private function discoverPersistenceId(): ?string
    {
        $persistence = null;

        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getServiceManager()->get(PersistenceManager::SERVICE_ID);

        foreach (self::PERSISTENCE_PRECEDENCE as $possiblePersistence) {
            if ($persistenceManager->hasPersistence($possiblePersistence)) {
                $persistence = $possiblePersistence;
                break;
            }
        }

        return $persistence;
    }
}
