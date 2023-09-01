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
 * Copyright (c) 2014-2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\install;

use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\oatbox\mutex\LockService;
use oat\oatbox\service\ServiceNotFoundException;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * This post-installation script configure lockService to use redis as store.
 * - Must have `--persistence` option as redis persistence for lock service
 *
 */
class SetUpLockService extends ScriptAction
{
    public function run()
    {

        $this->checkPersistance();
        $service = new LockService([
            LockService::OPTION_PERSISTENCE_CLASS   => RedisStore::class,
            LockService::OPTION_PERSISTENCE_OPTIONS => $this->getOption('persistence'),
        ]);
        $this->getServiceManager()->register(LockService::SERVICE_ID, $service);
        return \common_report_Report::createSuccess('LockService successfully configured.');
    }

    /**
     * Provides option of script
     *
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'persistence' => [
                'prefix'      => 'p',
                'longPrefix'  => 'persistence',
                'required'    => true,
                'description' => 'Redis persistence for lock service',
            ],
            'verbose'     => [
                'prefix'      => 'v',
                'longPrefix'  => 'verbose',
                'flag'        => true,
                'description' => 'Output the log as command output.',
            ],
        ];
    }

    /**
     * Check option and persistence existence
     *
     */
    private function checkPersistance()
    {
        if (empty($this->getOption('persistence'))) {
            throw new \common_Exception('No persistence specified');
        }
        $persistenceManager = $this->getServiceManager()->get(PersistenceManager::SERVICE_ID);
        $persistenceId = $this->getOption('persistence');
        if (!$persistenceManager->hasPersistence($persistenceId)) {
            throw new \common_Exception('Persistence not exists');
        }
        $persistence = $persistenceManager->getPersistenceById($persistenceId);
        if (!$persistence->getDriver() instanceof \common_persistence_PhpRedisDriver) {
            throw new \common_exception_InconsistentData('Not redis persistence id configured for RedisStore');
        }
    }

    /**
     * Provides description of the script
     *
     * @return string
     */
    protected function provideDescription()
    {
        return 'Setup lock service with redis persistence';
    }
}
