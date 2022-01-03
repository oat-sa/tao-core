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

declare(strict_types = 1);

namespace oat\tao\model\extension;

use common_Exception;
use common_exception_Error;
use common_ext_UpdateExtensions;
use common_ext_UpdaterNotFoundException as UpdaterNotFoundException;
use common_Logger;
use Exception;
use helpers_ExtensionHelper;
use helpers_Report;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\oatbox\extension\exception\ManifestException;
use oat\oatbox\log\LoggerAggregator;
use oat\oatbox\reporting\Report;
use oat\tao\model\asset\AssetService;
use oat\tao\model\event\TaoUpdateEvent;
use oat\tao\model\migrations\MigrationsService;
use common_ext_ExtensionsManager as ExtensionManager;
use common_ext_Extension as Extension;
use tao_models_classes_LanguageService;

/**
 * Extends the generis updater to take into account the translation files
 */
class UpdateExtensions extends common_ext_UpdateExtensions
{
    use EventManagerAwareTrait;

    /**
     * @throws common_exception_Error
     * @throws common_Exception
     */
    public function __invoke($params = []): Report
    {
        $this->setLogger(new LoggerAggregator([
            $this->getLogger(),
            $this->getServiceLocator()->get(UpdateLogger::SERVICE_ID),
        ]));

        $report = parent::__invoke($params);

        $migrationsReport = $this->getServiceLocator()->get(MigrationsService::class)->migrate();
        $this->logInfo(helpers_Report::renderToCommandline($migrationsReport, false));
        $report->add($migrationsReport);

        // regenerate locales
        $files = tao_models_classes_LanguageService::singleton()->generateAll();

        $report->add(
            count($files) > 0
                ? Report::createSuccess(__('Successfully updated %s client translation bundles', count($files)))
                : Report::createError(__('No client translation bundles updated'))
        );

        $updateId = $this->generateUpdateId();
        $this->updateCacheBuster($report, $updateId);

        $report->add($this->runPostUpdateScripts());
        $report->add(Report::createInfo(__('Update ID : %s', $updateId)));

        $this->getEventManager()->trigger(new TaoUpdateEvent($report));

        return $report;
    }

    /**
     * Generate a unique ID per update
     */
    protected function generateUpdateId(): string
    {
        return uniqid('', true);
    }

    /**
     * Update the asset service to save the cache buster value (the update id)
     * @throws common_exception_Error
     */
    private function updateCacheBuster(Report $report, string $updateId): void
    {
        try {
            $assetService = $this->getServiceLocator()->get(AssetService::SERVICE_ID);
            $assetService->setCacheBuster($updateId);
            $this->getServiceLocator()->register(AssetService::SERVICE_ID, $assetService);
        } catch (Exception $e) {
            common_Logger::e($e->getMessage());
            $report->add(Report::createWarning(__('Unable to update the asset service')));
        }
    }

    /**
     * @throws common_exception_Error
     */
    private function runPostUpdateScripts(): Report
    {
        $report = Report::createInfo('Post-update actions:');

        /** @var ExtensionManager $extensionManager */
        $extensionManager = $this->getServiceLocator()->get(ExtensionManager::SERVICE_ID);

        $extensions = helpers_ExtensionHelper::sortByDependencies($extensionManager->getInstalledExtensions());

        foreach ($extensions as $extension) {
            $postUpdateReport = $this->runPostUpdateScript($extension);

            if ($postUpdateReport !== null) {
                $report->add($postUpdateReport);
            }
        }

        if (!$report->hasChildren()) {
            $report->add(Report::createInfo('No actions to be executed'));
        }

        return $report;
    }

    private function runPostUpdateScript(Extension $ext): ?Report
    {
        try {
            return $ext->getUpdater()->postUpdate();
        } catch (UpdaterNotFoundException $e) {
            return Report::createSuccess(sprintf('No postprocessing defined for %s', $ext->getName()));
        } catch (ManifestException $e) {
            return Report::createWarning($e->getMessage());
        }
    }
}
