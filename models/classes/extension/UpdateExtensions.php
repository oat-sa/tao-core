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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\extension;

use common_report_Report;
use oat\oatbox\log\LoggerAggregator;
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\model\asset\AssetService;
use oat\tao\model\migrations\MigrationsService;
use oat\tao\scripts\tools\Migrations;
use common_ext_ExtensionsManager as ExtensionsManager;

/**
 * Extends the generis updater to take into account
 * the translation files
 */
class UpdateExtensions extends \common_ext_UpdateExtensions
{

    /**
     * (non-PHPdoc)
     * @see \oat\oatbox\action\Action::__invoke()
     */
    public function __invoke($params)
    {
        try {
            $loggers = [
                $this->getLogger(),
                $this->getServiceLocator()->get(UpdateLogger::SERVICE_ID)
            ];
            $this->setLogger(new LoggerAggregator($loggers));
        } catch (ServiceNotFoundException $e) {
            // update script to add update logger hasn't run yet, ignore
        }
        $report = parent::__invoke($params);

        $migrationsReport = $this->getServiceLocator()->get(MigrationsService::class)->migrate();
        $this->logInfo(\helpers_Report::renderToCommandline($migrationsReport, false));
        $report->add($migrationsReport);

        // regenerate locales
        $files = \tao_models_classes_LanguageService::singleton()->generateAll();
        if (count($files) > 0) {
            $report->add(new common_report_Report(common_report_Report::TYPE_SUCCESS, __('Successfully updated %s client translation bundles', count($files))));
        } else {
            $report->add(new common_report_Report(common_report_Report::TYPE_ERROR, __('No client translation bundles updated')));
        }

        $updateId = $this->generateUpdateId();
        $this->updateCacheBuster($report, $updateId);

        $postUpdateReport = $this->runPostUpdateScripts();
        $report->add($postUpdateReport);

        $report->add(new common_report_Report(common_report_Report::TYPE_INFO, __('Update ID : %s', $updateId)));

        return $report;
    }

    /**
     * Generate a unique ID per update
     * @return string the new id
     */
    protected function generateUpdateId()
    {
        return uniqid();
    }

    /**
     * Update the asset service to save the cache buster value (the update id)
     * @param common_report_Report $report
     * @param string               $updateid
     */
    private function updateCacheBuster(common_report_Report $report, $updateid)
    {
        try {
            $assetService = $this->getServiceLocator()->get(AssetService::SERVICE_ID);
            $assetService->setCacheBuster($updateid);
            $this->getServiceLocator()->register(AssetService::SERVICE_ID, $assetService);
        } catch (\Exception $e) {
            \common_Logger::e($e->getMessage());
            $report->add(new common_report_Report(common_report_Report::TYPE_WARNING, __('Unable to update the asset service')));
        }
    }

    /**
     * @throws \common_exception_Error
     */
    private function runPostUpdateScripts()
    {
        $report = new common_report_Report(common_report_Report::TYPE_INFO, 'Post update actions:');
        $extManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
        $sorted = \helpers_ExtensionHelper::sortByDependencies($extManager->getInstalledExtensions());
        foreach ($sorted as $ext) {
            try {
                $postUpdateExtensionReport = $ext->getUpdater()->postUpdate();
                if ($postUpdateExtensionReport) {
                    $report->add($postUpdateExtensionReport);
                }
            } catch (\common_ext_ManifestException $e) {
                $report->add(new common_report_Report(common_report_Report::TYPE_WARNING, $e->getMessage()));
            }
        }
        if (!$report->hasChildren()) {
            $report->add(new common_report_Report(common_report_Report::TYPE_INFO, 'No actions to be executed'));
        }
        return $report;
    }
}
