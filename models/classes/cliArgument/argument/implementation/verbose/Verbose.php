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

namespace oat\tao\model\cliArgument\argument\implementation\verbose;

use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerService;
use oat\oatbox\log\VerboseLogger;
use oat\tao\model\cliArgument\argument\Argument;
use Psr\Log\LoggerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

abstract class Verbose implements Argument, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Return the Psr3 logger level minimum to send log to logger
     *
     * @return string
     */
    abstract protected function getMinimumLogLevel();

    /**
     * Propagate the argument process to Action
     * To load a verbose logger, a check is done Action interfaces to find LoggerInterface
     * The verbose logger is loaded with the minimum level requires
     *
     * @param Action $action
     */
    public function load(Action $action)
    {
        if ($action instanceof LoggerAwareInterface) {
            $action->setLogger(
                $this->getServiceLocator()->get(LoggerService::SERVICE_ID)
                    ->addLogger(new VerboseLogger($this->getMinimumLogLevel()))
            );
        }
    }
}