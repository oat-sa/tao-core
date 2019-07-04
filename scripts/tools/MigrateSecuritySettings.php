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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\scripts\tools;

use common_report_Report as Report;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\service\SettingsStorage;
use oat\tao\model\settings\CspHeaderSettingsInterface;
use oat\tao\model\settings\SettingsStorageInterface;

class MigrateSecuritySettings extends AbstractAction
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
        if (isset($params[0])) {
            $this->wetRun = (bool) $params[0];
        }

        $wetInfo = ($this->wetRun === false) ? 'dry' : 'wet';
        $this->report = new Report(
            Report::TYPE_INFO,
            "Migrate Security Settings to the 'default_kv' persistence (${wetInfo} run)..."
        );

        $currentHeaderSetting = 'self';
        $currentHeaderList = [];
        try {
            /** @var SettingsStorageInterface $settingsStorage */
            $settingsStorage = $this->getServiceManager()->get(SettingsStorageInterface::SERVICE_ID);
            if ($settingsStorage->exists(CspHeaderSettingsInterface::CSP_HEADER_SETTING) !== false)
            {
                $currentHeaderSetting = $settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_SETTING);
            }

            if ($settingsStorage->exists(CspHeaderSettingsInterface::CSP_HEADER_LIST) !== false)
            {
                $currentHeaderList = $settingsStorage->get(CspHeaderSettingsInterface::CSP_HEADER_LIST);
            }

            $this->setDefaultKvPersistence();

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
    private function setDefaultKvPersistence()
    {
        $options = [
            SettingsStorage::OPTION_PERSISTENCE => 'default_kv',
            SettingsStorage::OPTION_KEY_NAMESPACE => 'tao:settings:'
        ];

        if ($this->wetRun) {
            $this->getServiceManager()->unregister(SettingsStorageInterface::SERVICE_ID);
            $settingsStorage = new SettingsStorage($options);
            $this->getServiceManager()->register(SettingsStorageInterface::SERVICE_ID, $settingsStorage);
            $this->report->add(Report::createInfo('SettingsStorage registered with new options: ' . print_r($options, true)));
        } else {
            $this->report->add(Report::createInfo('SettingsStorage will be registered with new options: ' . print_r($options, true)));
        }
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
