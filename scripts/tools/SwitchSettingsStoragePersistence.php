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

namespace oat\tao\scripts\tools;

use common_persistence_Manager;
use \common_report_Report as Report;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\service\SettingsStorage;
use oat\tao\model\settings\CspHeaderSettingsInterface;
use oat\tao\model\settings\SettingsStorageInterface;

/**
 * Script switch SettingsStorage persistence to redis (default RDS if redis is not configured yet)
 */
class SwitchSettingsStoragePersistence extends AbstractAction
{
    /**
     * @var boolean
     */
    protected $wetRun = true;

    /**
     * @var Report
     */
    protected $report;

    public function __invoke($params)
    {
        $this->params = $params;

        // Should we make a wet run?
        if (isset($this->params[0])) {
            $this->wetRun = (bool) $this->params[0];
        }

        $wetInfo = ($this->wetRun === false) ? 'dry' : 'wet';
        $this->report = new Report(
            Report::TYPE_INFO,
            "Switch SettingsStorage persistence to existing KeyValue or default RDS KeyValue implementation (${wetInfo} run)..."
        );

        try {
            $settingsStorage = $this->getServiceManager()->get(SettingsStorage::SERVICE_ID);

            $currentHeaderSetting = 'self';
            $currentHeaderList = [];
            if ($settingsStorage->exists(CspHeaderSettingsInterface::CSP_HEADER_SETTING) !== false)
            {
                $currentHeaderSetting = $settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_SETTING);
            }

            if ($settingsStorage->exists(CspHeaderSettingsInterface::CSP_HEADER_LIST) !== false)
            {
                $currentHeaderList = $settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_LIST);
            }

            $this->switchPersistence();
            $this->registerSettingsStorage();

            $this->migrateConfigs($currentHeaderSetting, $currentHeaderList);

            return $this->report;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->report->add(Report::createFailure($e->getMessage()));
        }
    }

    /**
     * @throws \common_Exception
     */
    private function switchPersistence()
    {
        /** @var common_persistence_Manager $persistenceManager */
        $persistenceManager = $this->getServiceManager()->get(common_persistence_Manager::SERVICE_ID);

        $persistencesConfig = $persistenceManager->getOption('persistences');
        $persistenceCandidates = array_keys($persistencesConfig);
        array_unshift($persistenceCandidates, 'serviceState', 'redis');

        if ($persistenceManager->hasPersistence('default_kv')) {
            $this->report->add(Report::createInfo('"default_kv" persistence already configured.'));
            return;
        }

        // By default if there is no redis persistence on the server fall back to RDS KV implementation
        $newPersistenceId = 'default';
        $newPersistenceConfig = [
            'driver' => \common_persistence_SqlKvDriver::class,
            \common_persistence_SqlKvDriver::OPTION_PERSISTENCE_SQL => 'default'
        ];

        foreach ($persistenceCandidates as $persistenceId) {
            if ($this->canUsePersistence($persistenceId)) {
                $newPersistenceId = $persistenceId;
                $newPersistenceConfig = $persistencesConfig[$persistenceId];

                break;
            }
        }

        if ($this->wetRun) {
            $persistenceManager->registerPersistence('default_kv', $newPersistenceConfig);
            $this->getServiceManager()->register(common_persistence_Manager::SERVICE_ID, $persistenceManager);

            $this->report->add(Report::createSuccess('SettingsStorage persistence config switched to: ' . print_r($newPersistenceConfig, true)));
        } else {
            $this->report->add(Report::createInfo("SettingsStorage persistence will be switched to the same as for '{$newPersistenceId}': " . print_r($newSettingsPersistence, true)));
        }
    }

    /**
     * CHeck if we can use persistence for settings storage
     *
     * @param string $persistenceId
     * @return bool
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    private function canUsePersistence($persistenceId)
    {
        /** @var common_persistence_Manager $persistenceManager */
        $persistenceManager = $this->getServiceManager()->get(common_persistence_Manager::SERVICE_ID);

        if (!$persistenceManager->hasPersistence($persistenceId)) {
            return false;
        }

        $persistence = $persistenceManager->getPersistenceById($persistenceId);
        if (!$persistence instanceof \common_persistence_KeyValuePersistence) {
            return false;
        }

        return true;
    }

    /**
     * @throws \common_Exception
     */
    private function registerSettingsStorage()
    {
        $this->getServiceManager()->unregister(SettingsStorageInterface::SERVICE_ID);

        $settingsStorage = new SettingsStorage([
            SettingsStorage::OPTION_PERSISTENCE => 'default_kv',
            SettingsStorage::OPTION_KEY_NAMESPACE => 'tao:settings:'
        ]);
        $this->getServiceManager()->register(SettingsStorageInterface::SERVICE_ID, $settingsStorage);
    }

    /**
     * @param string $currentHeaderSetting
     * @param string $currentHeaderList
     * @throws \common_exception_Error
     */
    private function migrateConfigs($currentHeaderSetting, $currentHeaderList)
    {
        /** @var SettingsStorageInterface $settingsStorage */
        $settingsStorage = $this->getServiceLocator()->get(SettingsStorageInterface::SERVICE_ID);
        if ($this->wetRun) {
            $settingsStorage->set(CspHeaderSettingsInterface::CSP_HEADER_SETTING, $currentHeaderSetting);
            $settingsStorage->set(CspHeaderSettingsInterface::CSP_HEADER_LIST, $currentHeaderList);

            $this->report->add(Report::createSuccess('Settings set to: ' . print_r([
                CspHeaderSettingsInterface::CSP_HEADER_SETTING => $currentHeaderSetting,
                CspHeaderSettingsInterface::CSP_HEADER_LIST => $currentHeaderList
            ], true)));
        } else {
            $this->report->add(Report::createInfo('Settings will be set to: ' . print_r([
                CspHeaderSettingsInterface::CSP_HEADER_SETTING => $currentHeaderSetting,
                CspHeaderSettingsInterface::CSP_HEADER_LIST => $currentHeaderList
            ], true)));
        }
    }
}
