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
 * Copyright (c) 2016-2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\routing;

use oat\oatbox\action\ActionService;
use oat\oatbox\action\ResolutionException;
use common_report_Report as Report;
use oat\tao\model\cliArgument\ArgumentService;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\oatbox\service\ServiceManagerAwareInterface;

/**
 * Class CliController
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\routing
 */
class CliController implements ServiceManagerAwareInterface
{
    use ServiceManagerAwareTrait;

    /**
     * @param string $actionIdentifier fully qualified action class name
     * @param array $params Params to be passed to action's __invoke method
     * @return Report
     */
    public function runAction($actionIdentifier, array $params = [])
    {
        try {
            $actionService = $this->getServiceLocator()->get(ActionService::SERVICE_ID);
            $action = $actionService->resolve($actionIdentifier);
        } catch (ResolutionException $e) {
            return new Report(Report::TYPE_ERROR, $e->getMessage());
        }

        $this->propagate($action);

        $this->getServiceLocator()->get(ArgumentService::SERVICE_ID)->load($action, $params);

        try {
            $report = call_user_func($action, $params);
            if (empty($report)) {
                $shortName = (new \ReflectionClass($action))->getName();
                $report = new \common_report_Report(
                    \common_report_Report::TYPE_INFO,
                    "Action '{$shortName}' ended gracefully with no report returned."
                );
            }
        } catch (\Exception $e) {
            $report = new Report(Report::TYPE_ERROR, __('An exception occured while running "%s"', $actionIdentifier));

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
}
