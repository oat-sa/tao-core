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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\install;

use Doctrine\DBAL\Schema\SchemaException;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\oauth\lockout\IPFactory;
use oat\tao\model\oauth\lockout\IPLockout;
use oat\tao\model\oauth\lockout\LockoutInterface;
use oat\tao\model\oauth\lockout\NoLockout;
use oat\tao\model\oauth\lockout\storage\KvLockoutStorage;
use oat\tao\model\oauth\lockout\storage\RdsLockoutStorage;
use oat\tao\model\oauth\OauthService;

/**
 * Class SetUpOAuthLockoutService
 * Setup Lockout service for lti launches. Flags described in provideOptions()
 * Enable:
 * [sudo -u www-data] php index.php 'oat\tao\scripts\install\SetUpOAuthLockoutService' -n IpLockout -s rds -o 300 -t 100
 * Disable:
 * [sudo -u www-data] php index.php 'oat\tao\scripts\install\SetUpOAuthLockoutService'
 * @package oat\tao\scripts\install
 */
class SetUpOAuthLockoutService extends ScriptAction
{
    public const OPT_VERBOSE = 'verbose';
    public const OPT_LOCK = 'implementation';
    public const OPT_STORAGE = 'storage';
    public const OPT_THRESHOLD = 'threshold';
    public const OPT_TIMEOUT = 'timeout';

    public const STORAGE_KV = 'kv';
    public const STORAGE_RDS = 'rds';

    public const SERVICE_NOLOCK = 'NoLockout';
    public const SERVICE_IPLOCK = 'IpLockout';

    /**
     * flag to run migration to rds after successful registering service
     * @var bool
     */
    private $runMigration = false;

    protected function provideOptions()
    {
        return [
            self::OPT_LOCK      => [
                'prefix'      => 'n',
                'longPrefix'  => self::OPT_LOCK,
                'cast'        => 'string',
                'default'     => self::SERVICE_NOLOCK,
                'description' => 'Default NoLockout implementation, otherwise it will require defining storage as well. [IpLockout|NoLockout]',
            ],
            self::OPT_STORAGE   => [
                'prefix'      => 's',
                'longPrefix'  => self::OPT_STORAGE,
                'required'    => false,
                'cast'        => 'string',
                'default'     => 'rds',
                'description' => 'Storage for lockout service, default rds. [rds|kv]',
            ],
            self::OPT_TIMEOUT   => [
                'prefix'      => 'o',
                'required'    => false,
                'cast'        => 'integer',
                'longPrefix'  => self::OPT_TIMEOUT,
                'default'     => 300,
                'description' => 'Timeout for block in seconds. Default 300 seconds',
            ],
            self::OPT_THRESHOLD => [
                'prefix'      => 't',
                'required'    => false,
                'cast'        => 'integer',
                'longPrefix'  => self::OPT_THRESHOLD,
                'default'     => 100,
                'description' => 'Number attempts before block. Default 100',
            ],
            self::OPT_VERBOSE   => [
                'prefix'      => 'v',
                'longPrefix'  => self::OPT_VERBOSE,
                'flag'        => true,
                'description' => 'Output the log as command output.',
            ],
        ];
    }

    public function run()
    {
        if ($this->getOption(self::OPT_LOCK) === self::SERVICE_IPLOCK) {
            $lockOutService = $this->setIpLockout();
        } else {
            $lockOutService = $this->setNoLockout();
        }
        $oauthService = $this->getServiceManager()->get(OauthService::SERVICE_ID);
        $oauthService->setOption(OauthService::OPTION_LOCKOUT_SERVICE, $lockOutService);
        $this->getServiceManager()->register(OauthService::SERVICE_ID, $oauthService);
        if ($this->runMigration) {
            $storageService = $this->getServiceManager()
                ->get(OauthService::SERVICE_ID)
                ->getSubService(OauthService::OPTION_LOCKOUT_SERVICE)
                ->getSubService(IPLockout::OPTION_LOCKOUT_STORAGE);
            if ($storageService instanceof RdsLockoutStorage) {
                $persistenceId = $storageService->getPersistenceId();
                $persistence = $this->getServiceLocator()
                    ->get(PersistenceManager::SERVICE_ID)
                    ->getPersistenceById($persistenceId);

                $schemaManager = $persistence->getDriver()->getSchemaManager();
                $schema = $schemaManager->createSchema();
                $fromSchema = clone $schema;
                try {
                    $schema = $storageService->getSchema($schema);
                    $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
                    foreach ($queries as $query) {
                        $persistence->exec($query);
                    }
                } catch (SchemaException $e) {
                    $this->logInfo('Database Schema already up to date.');
                }
            }
        }
    }

    protected function setNoLockout(): LockoutInterface
    {
        return new NoLockout();
    }

    protected function setIpLockout(): LockoutInterface
    {
        $options[IPLockout::OPTION_THRESHOLD] = $this->getOption(self::OPT_THRESHOLD);
        $options[IPLockout::OPTION_TIMEOUT] = $this->getOption(self::OPT_TIMEOUT);
        $options[IPLockout::OPTION_IP_FACTORY] = new IPFactory();
        switch ($this->getOption(self::OPT_STORAGE)) {
            case self::STORAGE_KV:
                $options[IPLockout::OPTION_LOCKOUT_STORAGE] = new KvLockoutStorage([KvLockoutStorage::OPTION_PERSISTENCE => 'default_kv']);
                break;
            case self::STORAGE_RDS:
                $options[IPLockout::OPTION_LOCKOUT_STORAGE] = new RdsLockoutStorage([RdsLockoutStorage::OPTION_PERSISTENCE => 'default']);
                $this->runMigration = true;
                break;
            default:
                throw new \common_Exception('Incorrect persistence');
        }
        return new IPLockout($options);
    }

    protected function provideDescription()
    {
        return 'Script sets up and configures selected lockout implementation.';
    }
}
