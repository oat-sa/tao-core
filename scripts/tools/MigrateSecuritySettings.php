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
use common_Exception;
use oat\oatbox\extension\AbstractAction;
use oat\tao\model\service\SettingsStorage;
use oat\tao\model\settings\CspHeaderSettingsInterface;
use oat\tao\model\settings\SettingsStorageInterface;

class MigrateSecuritySettings extends AbstractAction
{
    /**
     * @var boolean
     */
    protected $wetRun = false;

    /**
     * @var SettingsStorageInterface
     */
    protected $oldSettingsStorage;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @param array $params
     * @return Report
     * @throws common_Exception
     */
    public function __invoke($params)
    {
        if (count($params) == 0) {
            return new Report(
                Report::TYPE_ERROR,
                "Usage: MigrateSecuritySettings OLD_PERSISTENCE_ID [--wet]"
            );
        }
        $this->oldSettingsStorage = new SettingsStorage([
            SettingsStorage::OPTION_PERSISTENCE => $params[0]
        ]);
        $this->propagate($this->oldSettingsStorage);

        $this->wetRun = in_array('--wet', $params);
        $wetInfo = $this->wetRun ? 'wet' : 'dry';

        $currentPersistence = $this->getServiceLocator()
            ->get(SettingsStorageInterface::SERVICE_ID)
            ->getOption(SettingsStorage::OPTION_PERSISTENCE);

        $this->report = new Report(
            Report::TYPE_INFO,
            "Migrate Security Settings to the '$currentPersistence' persistence ({$wetInfo} run)..."
        );

        try {
            $settingsToMigrate = [
                CspHeaderSettingsInterface::CSP_HEADER_SETTING,
                CspHeaderSettingsInterface::CSP_HEADER_LIST
            ];

            foreach ($settingsToMigrate as $setting) {
                $this->migrateSetting($setting);
            }

            return $this->report;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->report->add(Report::createFailure($e->getMessage()));
        }
    }

    /**
     * @param string $settingName
     * @throws common_Exception
     */
    private function migrateSetting($settingName)
    {
        if ($this->oldSettingsStorage->exists($settingName)) {
            $settingValue = $this->oldSettingsStorage->get($settingName);
            if ($this->wetRun) {
                $settingsStorage = $this->getServiceLocator()->get(SettingsStorageInterface::SERVICE_ID);
                $settingsStorage->set($settingName, $settingValue);
            }

            $msg = $this->wetRun ? 'Settings set to: ' : 'Settings will be set to: ';
            $this->report->add(Report::createSuccess($msg . print_r([
                    $settingName => $settingValue
                ], true)));
        }
    }
}
