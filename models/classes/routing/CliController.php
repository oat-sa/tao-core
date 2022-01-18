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
 * Copyright (c) 2016-2021 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\routing;

use common_exception_Error;
use Exception;
use oat\oatbox\action\Action;
use oat\oatbox\action\ActionService;
use oat\oatbox\action\ResolutionException;
use oat\oatbox\reporting\Report;
use oat\oatbox\log\LoggerService;
use oat\oatbox\reporting\ReportInterface;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\cliArgument\ArgumentService;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\oatbox\service\ServiceManagerAwareInterface;
use tao_helpers_I18n;

class CliController implements ServiceManagerAwareInterface
{
    use ServiceManagerAwareTrait;

    /**
     * @throws common_exception_Error
     */
    public function runAction($actionIdentifier, array $params = [])
    {
        try {
            $action = $this->getResolver()->resolve($actionIdentifier);
        } catch (ResolutionException $e) {
            return new Report(ReportInterface::TYPE_ERROR, $e->getMessage());
        }

        $this->propagate($action);

        $this->loadTranslations($action);

        $this->getServiceLocator()->get(ArgumentService::SERVICE_ID)->load($action, $params);

        try {
            $report = call_user_func($action, $params);
            if (empty($report)) {
                $report = new Report(
                    ReportInterface::TYPE_INFO,
                    sprintf(
                        'Action \'%s\' ended gracefully with no report returned.',
                        $this->getResolver()->getActionName($action)
                    )
                );
            }
        } catch (Exception $e) {
            $report = new Report(
                Report::TYPE_ERROR,
                __('An exception has occurred while running "%s".', $actionIdentifier)
            );

            $message = $e->getMessage();
            $previous = $e->getPrevious();

            // Get the full stack trace of the exception
            while ($previous) {
                $message .= PHP_EOL . "caused by : " . PHP_EOL . $previous->getMessage();
                $previous = $previous->getPrevious();
            }

            $report->add(new Report(Report::TYPE_ERROR, $message));
        }

        return $report;
    }

    private function loadTranslations(Action $action): void
    {
        try {
            tao_helpers_I18n::init(
                $this->getResolver()->getActionExtension($action),
                $this->getServiceLocator()->get(UserLanguageServiceInterface::SERVICE_ID)->getDefaultLanguage()
            );
        } catch (ResolutionException $e) {
            $this->getServiceLocator()->get(LoggerService::SERVICE_ID)->notice('Unable to load translations.');
        }
    }

    private function getResolver(): ActionService
    {
        return $this->getServiceLocator()->get(ActionService::SERVICE_ID);
    }
}
