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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\routing;

use oat\oatbox\service\ServiceManager;
use oat\oatbox\action\ActionService;
use oat\oatbox\action\ResolutionException;
use common_report_Report as Report;
use oat\oatbox\action\Help;
use oat\tao\model\cliArgument\ArgumentService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class CliController
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\routing
 */
class CliController implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var ActionService
     */
    protected $actionService;

    /**
     * CliController constructor.
     */
    public function __construct()
    {
        $this->setServiceLocator(ServiceManager::getServiceManager());
        $this->actionService = $this->getServiceLocator()->get(ActionService::SERVICE_ID);
    }

    /**
     * @param string $actionIdentifier fully qualified action class name
     * @param array $params Params to be passed to action's __invoke method
     * @return Report
     */
    public function runAction($actionIdentifier, array $params = [])
    {
        try {
            $action = $this->actionService->resolve($actionIdentifier);
        } catch (\common_ext_ManifestNotFoundException $e) {
            $action = new Help(null);
        } catch (ResolutionException $e) {
            $parts = explode('/', $actionIdentifier);
            $extId = $parts[0];
            $action = new Help($extId);
        }

        if ($action instanceof ServiceLocatorAwareInterface) {
            $action->setServiceLocator($this->getServiceLocator());
        }

        $this->getServiceLocator()->get(ArgumentService::SERVICE_ID)->load($action, $params);

        try {
            $report = call_user_func($action, $params);
        } catch (\Exception $e) {
            $report = new Report(Report::TYPE_ERROR, __('An exception occured while running "%s"', $actionIdentifier));
            $report->add(new Report(Report::TYPE_ERROR, $e->getMessage()));
        }

        return $report;
    }
}